<?php

namespace App\Events\Room;

use App\Channels\PresenceRoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeftEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	private Room $room;
	private User $user;


	/**
	 * Create a new event instance.
	 *
	 * @param Room $room
	 * @param User $user
	 */
	public function __construct(Room $room, User $user)
	{
		$this->room = $room;
		$this->user = $user;
	}


	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
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
			"player" => $this->user,
		];
	}
}
