<?php

namespace App\Cache;


use App\Room;

class RoomRound extends CacheModel
{
	public const STATE_DRAW_BLACK_CARD = "draw:blackCard";
	public const STATE_DRAW_WHITE_CARD = "draw:whiteCard";
	public const STATE_REVEAL_CARDS = "reveal:cards";
	public const STATE_REVEAL_USERNAMES = "scores:usernames";
	public const STATE_LEADERBOARD = "leaderboard";

	public string $state;
	public ?int $black_card_id;

	private Room $room;


	public function __construct(Room $room)
	{
		$this->room = $room;
		parent::__construct('Room', $room->id, 'Round');
	}


	public function refresh(): self
	{
		$attributes = $this->fetchRawAttributes();
		$this->state = $attributes["state"] ?? self::STATE_DRAW_BLACK_CARD;
		$this->black_card_id = $attributes["black_card_id"] ?? null;
		return $this;
	}


	public static function find(Room $room): self
	{
		return (new self($room))->refresh();
	}


	/**
	 * @inheritDoc
	 */
	public function toArray()
	{
		return [
			"state" => $this->state,
			"black_card_id" => $this->black_card_id,
		];
	}
}