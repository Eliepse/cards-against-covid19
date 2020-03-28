<?php


namespace App\Channels;


use App\Room;
use App\User;
use Illuminate\Broadcasting\PresenceChannel;

class PrivateRoomChannel extends PresenceChannel
{
	/**
	 * @var Room
	 */
	private Room $room;

	/**
	 * @var User
	 */
	private User $user;


	public function __construct(Room $room, User $user)
	{
		parent::__construct("App.Room.{$room->id}");
		$this->room = $room;
		$this->user = $user;
	}


	public function join(User $user)
	{
		//
	}


	public function getChannelId(): string
	{
		return "App.Room.{$this->room->id}.{$this->user->id}";
	}
}