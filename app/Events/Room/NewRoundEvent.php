<?php

namespace App\Events\Room;

use App\Card;
use App\Channels\PrivateRoomChannel;
use App\Room;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRoundEvent implements ShouldBroadcast
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
	 * @return PrivateRoomChannel
	 */
	public function broadcastOn(): PrivateRoomChannel
	{
		return new PrivateRoomChannel($this->room, $this->user);
	}


	public function broadcastWith(): array
	{
		$cards = Card::query()
			->whereIn('id', $this->room->hands->getForUser($this->user))
			->select(['id', 'text'])
			->get();

		return [
			"round" => $this->room->round,
			"hand" => $cards,
		];
	}
}
