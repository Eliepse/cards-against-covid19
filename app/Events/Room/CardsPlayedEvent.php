<?php

namespace App\Events\Room;

use App\Channels\PresenceRoomChannel;
use App\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardsPlayedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	private Room $room;
	private int $amount;


	/**
	 * Create a new event instance.
	 *
	 * @param Room $room
	 * @param int $amount
	 */
	public function __construct(Room $room, int $amount)
	{
		$this->room = $room;
		$this->amount = $amount;
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
			"room" => $this->room,
			"round" => $this->room->round,
			"amount" => $this->amount,
		];
	}
}
