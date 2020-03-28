<?php


namespace App\Channels;


use App\Room;
use App\User;
use Illuminate\Broadcasting\PresenceChannel;

class PresenceRoomChannel extends PresenceChannel
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


	public function join(User $user)
	{
		//
	}


	public function getChannelId(): string
	{
		return "App.Room.{$this->room->id}";
	}
}