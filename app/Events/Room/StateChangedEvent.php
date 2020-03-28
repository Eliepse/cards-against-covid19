<?php

namespace App\Events\Room;

use App\Channels\PresenceRoomChannel;
use App\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class StateChangedEvent
 * Occures whenever the actual round change stage(state): start/new, selection, reveal, recap, end
 *
 * @package App\Events\Room
 */
class StateChangedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * @var Room
	 */
	private Room $room;


	/**
	 * Create a new event instance.
	 *
	 * @param Room $room
	 */
	public function __construct(Room $room)
	{
		$this->room = $room;
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


	public function broadcastWith(): array
	{
		return [
			"round" => $this->room->round,
			"room" => $this->room,
		];
	}
}
