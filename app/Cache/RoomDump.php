<?php

namespace App\Cache;


use App\Room;

class RoomDump extends CacheModel
{
	public array $cards_id;

	private Room $room;


	public function __construct(Room $room)
	{
		$this->room = $room;
		parent::__construct('Room', $room->id, 'Dump');
	}


	public function refresh(): self
	{
		$this->cards_id = $this->fetchRawAttributes();
		return $this;
	}


	public static function find(Room $room): self
	{
		return (new self($room))->refresh();
	}


	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		return $this->cards_id;
	}
}