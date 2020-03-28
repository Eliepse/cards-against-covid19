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
	public array $players_played_cards_ids;

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
		$this->players_played_cards_ids = $attributes["players_played_cards_ids"] ?? [];
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
		if (empty($this->players_played_cards_ids))
			return collect();
		$ids = Arr::flatten($this->players_played_cards_ids);
		$this->white_cards = $this->white_cards ?: collect(Card::fetchHandCardsList($ids));
		return $this->white_cards;
	}


	public function getWhiteCardsOf(User $user): Collection
	{
		return $this->getWhiteCards()->get($user->id);
	}


	public function setWhiteCardsOf(array $cards): self
	{

		return $this;
	}


	public function toArray(): array
	{
		return [
			"state" => $this->state,
			"black_card_id" => $this->black_card_id,
			"black_card" => $this->getBlackCard(),
			"players_played_cards_ids" => $this->players_played_cards_ids,
		];
	}


	/**
	 * @inheritDoc
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray());
	}
}