<?php

namespace App\Events\Room;

use App\Channels\PresenceRoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
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
	 * @return PresenceRoomChannel
	 */
	public function broadcastOn(): PresenceRoomChannel
	{
		return new PresenceRoomChannel($this->room);
	}


	/** @noinspection PhpUnused */
	public function broadcastWith(): array
	{
		return [
			"player" => $this->user,
			"players" => $this->room->players,
		];
	}
}
