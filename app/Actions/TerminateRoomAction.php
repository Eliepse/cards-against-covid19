<?php


namespace App\Actions;


use App\Room;

class TerminateRoomAction
{
	public function __invoke(Room $room)
	{
		$room->state = Room::STATE_TERMINATED;
		$room->round->delete();
		$room->dump->delete();
		$room->hands->delete();
		$room->save();
	}
}