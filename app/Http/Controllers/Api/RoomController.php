<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRoomRequest;
use App\Room;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoomController
{
	use AuthorizesRequests;


	public function __construct() { }


	/**
	 * @param Request $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function show(Request $request, Room $room)
	{
		$this->authorize('view', $room);
		$room->loadMissing(['host', 'players']);

		// TODO: fetch room state's data from cache

//		$cache_key = "App.Room.{$room->id}";

		return [
			"room" => $room,
			"state" => $room->state,
			"black_card" => null,
		];
	}


	/**
	 * @param StoreRoomRequest $request
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function store(StoreRoomRequest $request)
	{
		$this->authorize('create', Room::class);

		/** @var User $user */
		$user = $request->user();
		$room = new Room();
		$room->generateUrl();
		$room->max_players = $request->get('max_players', 8);
		$room->hand_size = 5; // TODO: change the size according to final player number?
		$user->hostedRooms()->save($room);
		$user->playedRooms()->attach($room);

		return $room->toArray();
	}


}