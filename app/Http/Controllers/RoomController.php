<?php


namespace App\Http\Controllers;


use App\Card;
use App\Events\Room\PlayerJoinedEvent;
use App\Room;
use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RoomController
{
	use AuthorizesRequests;


	public function show(string $url)
	{
		/** @var Room $room */
		$room = Room::query()->where('url', $url)->firstOrFail(['id', 'url', 'max_players', 'state']);

		$this->authorize('join', $room);

//		$cache_key = "App.Room.{$room->id}";

		$room->players()->syncWithoutDetaching(Auth::user());

		return view("room.show", ["room" => $room]);
	}
}