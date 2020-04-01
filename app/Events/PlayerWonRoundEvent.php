<?php

namespace App\Events;

use App\Channels\PresenceRoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerWonRoundEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;
	/**
	 * @var Room
	 */
	private Room $room;
	/**
	 * @var User
	 */
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


	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return PresenceRoomChannel
	 */
	public function broadcastOn(): PresenceRoomChannel
	{
		return new PresenceRoomChannel($this->room);
	}


	/**
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function broadcastWith(): array
	{
		return [
			"room" => $this->room,
			"round" => $this->room->round,
			"player" => $this->player,
		];
	}
}
