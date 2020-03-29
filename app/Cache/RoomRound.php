<?php

namespace App\Cache;


use App\Card;
use App\Room;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RoomRound extends CacheModel
{
	public const STATE_DRAW_BLACK_CARD = "draw:black-card";
	public const STATE_DRAW_WHITE_CARD = "draw:white-card";
	public const STATE_REVEAL_CARDS = "reveal:cards";
	public const STATE_REVEAL_USERNAMES = "reveal:usernames";
	public const STATE_LEADERBOARD = "leaderboard";

	public string $state;
	public ?int $black_card_id = null;
	public array $played_cards_ids = [];
	public array $played_ids = [];

	private Room $room;
	private ?Card $black_card = null;
	private Collection $white_cards;


	public function __construct(Room $room)
	{
		$this->room = $room;
		$this->white_cards = collect();
		parent::__construct('Room', $room->id, 'Round');
	}


	public function refresh(): self
	{
		$attributes = $this->fetchRawAttributes();
		$this->state = $attributes["state"] ?? self::STATE_DRAW_BLACK_CARD;
		$this->black_card_id = $attributes["black_card_id"] ?? null;
		$this->played_cards_ids = $attributes["played_cards_ids"] ?? [];
		$this->played_ids = $attributes["played_ids"] ?? [];
		return $this;
	}


	public static function find(Room $room): self
	{
		return (new self($room))->refresh();
	}


	public function getBlackCard(): ?Card
	{
		if (!is_numeric($this->black_card_id))
			return null;
		$this->black_card = $this->black_card ?: Card::query()->find($this->black_card_id, ['id', 'text', 'blanks']);
		return $this->black_card;
	}


	public function getWhiteCards(): Collection
	{
		if (empty($this->played_cards_ids))
			return collect();
		$ids = Arr::flatten($this->played_cards_ids);
		$cards = Card::query()->select(["id", "text"])->findMany($ids);

		if ($this->white_cards->isEmpty()) {
			$this->white_cards = collect(
				array_map(
					fn($c_ids) => $cards->whereInStrict("id", $c_ids),
					$this->played_cards_ids
				)
			);
		}

		return $this->white_cards;
	}


	public function getWhiteCardsOf(User $user): Collection
	{
		return $this->getWhiteCards()->get($user->id, collect());
	}


	public function addPlayedCardBy(User $user, array $ids): self
	{
		$this->played_cards_ids[ $user->id ] = array_merge(
			$this->played_cards_ids[ $user->id ] ?? [],
			$ids
		);
		return $this;
	}


	public function toArray(): array
	{
		return [
			"state" => $this->state,
			"black_card_id" => $this->black_card_id,
			"black_card" => $this->getBlackCard(),
			"played_ids" => $this->played_ids,
		];
	}


	public function toSaveArray(): array
	{
		return array_merge(
			parent::toSaveArray(),
			["played_cards_ids" => $this->played_cards_ids],
		);
	}
}