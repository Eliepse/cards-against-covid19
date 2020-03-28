<?php

namespace App\Http\Controllers\Api;

use App\Events\Room\StateChangedEvent;
use App\Http\Requests\StoreRoomRequest;
use App\Room;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
	public function show(Request $request, Room $room): array
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
	public function store(StoreRoomRequest $request): array
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


	/**
	 * @param Request $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function startPlaying(Request $request, Room $room)
	{
		// Check if the person have the RIGHT to do before checking if it's possible to do it

		if (!$room->host->is($request->user())) {
			throw new AuthorizationException("Only the host can start the game.");
		}

		if (!$room->isWaiting()) {
			throw new AuthorizationException("This is room is not waiting players anymore.");
		}

		if ($room->players->count() < 2) {
			throw new AuthorizationException("This room does not have enough players.");
		}

		$room->state = Room::STATE_PLAYING;
		$room->players_order = $room->players->shuffle()->pluck('id');
		$room->save();

		broadcast(new StateChangedEvent($room))->toOthers();

		return [
			"state" => $room->state,
			"room" => $room,
		];
	}
}