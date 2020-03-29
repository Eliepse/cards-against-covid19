<?php

namespace App\Cache;


use App\Room;
use App\User;

class RoomHands extends CacheModel
{
	public array $hands;

	private Room $room;


	public function __construct(Room $room)
	{
		$this->room = $room;
		parent::__construct('Room', $room->id, 'Hands');
	}


	public function refresh(): self
	{
		$this->hands = $this->fetchRawAttributes();
		return $this;
	}


	public static function find(Room $room): self
	{
		return (new self($room))->refresh();
	}


	public function getForUser(User $user): array
	{
		return $this->hands[ $user->id ] ?? [];
	}


	public function setForUser(User $u, array $card_ids): self
	{
		$this->hands[ $u->id ] = $card_ids;
		return $this;
	}


	public function addToUser(User $u, array $card_ids): self
	{
		$this->hands[ $u->id ] = array_merge($this->hands[ $u->id ] ?? [], $card_ids);
		return $this;
	}


	public function removeFromUser(User $u, array $card_ids): self
	{
		$this->hands[ $u->id ] = array_values(array_diff($this->hands[ $u->id ] ?? [], $card_ids));
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function toArray()
	{
		return $this->hands;
	}
}