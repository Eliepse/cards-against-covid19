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

	/**
	 * The current state of this round.
	 *
	 * @var string
	 */
	public string $state;

	/**
	 * The black card of this round.
	 *
	 * @var int|null
	 */
	public ?int $black_card_id = null;

	/**
	 * IDs of cards played, grouped by players' ID
	 *
	 * @var array
	 */
	public array $played_cards_ids = [];

	/**
	 * IDs of players, when they have played
	 * their white cards.
	 *
	 * @var array
	 */
	public array $played_ids = [];

	/**
	 * IDs of players, when their played
	 * card has been revealed by the juge.
	 *
	 * @var array
	 */
	public array $revealed_ids = [];

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
		$this->revealed_ids = $attributes["revealed_ids"] ?? [];
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


	public function reset(): self
	{
		$this->state = self::STATE_DRAW_BLACK_CARD;
		$this->black_card_id = null;
		$this->played_cards_ids = [];
		$this->played_ids = [];
		$this->revealed_ids = [];
		return $this;
	}


	public function toArray(): array
	{
		return [
			"state" => $this->state,
			"black_card_id" => $this->black_card_id,
			"black_card" => $this->getBlackCard(),
			"played_ids" => $this->played_ids,
			"played_cards_ids" => $this->played_cards_ids,
			"played_cards" => $this->getWhiteCards(),
			"revealed_ids" => $this->revealed_ids,
		];
	}


//	public function toSaveArray(): array
//	{
//		return array_merge(
//			parent::toSaveArray(),
//			["played_cards_ids" => $this->played_cards_ids],
//		);
//	}
}