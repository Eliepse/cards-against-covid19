<?php


namespace App\Http\Controllers;


use App\Events\Room\PlayerJoinedEvent;
use App\Http\Requests\StoreRoomRequest;
use App\Room;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends \Illuminate\Routing\Controller
{
	use AuthorizesRequests;


	public function __construct()
	{
		$this->middleware('auth');
	}


	/**
	 * @param string $url
	 *
	 * @return Factory|View
	 * @throws AuthorizationException
	 */
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


	/**
	 * @param StoreRoomRequest $request
	 *
	 * @return RedirectResponse
	 * @throws AuthorizationException
	 */
	public function store(StoreRoomRequest $request): RedirectResponse
	{
		$this->authorize('create', Room::class);

		/** @var User $user */
		$user = Auth::user();
		$room = new Room([
			"max_players" => $request->get('max_players', 8),
			"hand_size" => 5,
		]);
		$room->generateUrl();

		$user->hostedRooms()->save($room);
		$user->playedRooms()->attach($room);

		return redirect()->action([self::class, 'show'], $room->url);
	}
}