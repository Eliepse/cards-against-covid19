<?php

namespace App\Cache;


use App\Card;
use App\Room;
use Illuminate\Support\Collection;

class RoomDump extends CacheModel
{
	public array $ids = [];

	private Room $room;


	public function __construct(Room $room)
	{
		$this->room = $room;
		parent::__construct('Room', $room->id, 'Dump');
	}


	public function refresh(): self
	{
		$this->ids = $this->fetchRawAttributes();
		return $this;
	}


	public static function find(Room $room): self
	{
		return (new self($room))->refresh();
	}


	public function addCard(Card $card): self
	{
		$this->ids[] = $card->id;
		return $this;
	}


	public function addCards(Collection $cards): self
	{
		$cards->each(fn($card) => $this->addCard($card));
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		return $this->ids;
	}
}