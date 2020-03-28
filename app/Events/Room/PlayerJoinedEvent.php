<?php

namespace App\Events\Room;

use App\Channels\RoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PlayerJoinedEvent
 * Occures when a player join the room (different for just re-connecting to the room)
 *
 * @package App\Events\Room
 */
class PlayerJoinedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * @var Room
	 */
	private Room $room;

	/**
	 * @var User
	 */
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
	 * @return RoomChannel
	 */
	public function broadcastOn(): RoomChannel
	{
		return new RoomChannel($this->room);
	}


	public function broadcastWith(): array
	{
		return [
			"player" => $this->user,
			"players" => $this->room->players,
		];
	}
}
