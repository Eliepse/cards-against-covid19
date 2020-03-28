<?php


namespace App\Http\Controllers;


use App\Events\Room\PlayerJoinedEvent;
use App\Room;
use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class RoomController
{
	use AuthorizesRequests;


	public function show(string $url)
	{
		/** @var User $user */
		/** @var Room $room */
		$user = Auth::user();
		$room = Room::query()
			->where('url', $url)
			->without(['host'])
			->firstOrFail(['id', 'url', 'max_players', 'state']);

		$this->authorize('join', $room);

//		$cache_key = "App.Room.{$room->id}";

		// Add player and broadcast only once
		if (!$room->players->firstWhere("id", $user->id)) {
			$room->players()->syncWithoutDetaching($user);
			broadcast(new PlayerJoinedEvent($room, $user))->toOthers();
		}


		return view("room.show", ["room" => $room]);
	}
}