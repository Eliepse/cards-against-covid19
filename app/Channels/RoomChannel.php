<?php


namespace App\Channels;


use App\Room;
use Illuminate\Broadcasting\PresenceChannel;

class RoomChannel extends PresenceChannel
{
	/**
	 * @var Room
	 */
	private Room $room;


	public function __construct(Room $room)
	{
		parent::__construct("App.Room.{$room->id}");
		$this->room = $room;
	}


	public function getChannelId(): string
	{
		return "App.Room.{$this->room->id}";
	}
}