<?php

namespace App\Events\Room;

use App\Channels\PresenceRoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerRevealedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	private Room $room;
	private User $player;


	/**
	 * Create a new event instance.
	 *
	 * @param Room $room
	 * @param User $player
	 */
	public function __construct(Room $room, User $player)
	{
		$this->room = $room;
		$this->player = $player;
	}


	public function broadcastOn(): PresenceRoomChannel
	{
		return new PresenceRoomChannel($this->room);
	}


	/** @noinspection PhpUnused */
	public function broadcastWith(): array
	{
		return [
			"room" => $this->room,
			"round" => $this->room->round,
			"player" => $this->player,
		];
	}
}
