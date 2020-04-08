<?php

namespace App\Http\Controllers;

use App\Actions\TerminateRoomAction;
use App\Card;
use App\Room;
use App\User;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Renderable
	 */
	public function index()
	{
		/** @var User $user */
		$user = auth()->user();

		$room = $user->playedRooms()
			->where("state", "!=", Room::STATE_TERMINATED)
			->first(['id', 'url']);

		if ($room && $room->isStale()) {
			(new TerminateRoomAction())($room);
			$room = null;
		}

		return view('home', [
			"user" => $user,
			"room" => $room,
			"total" => Card::query()->count(),
			"totalSelf" => $user->cards()->count(),
		]);
	}
}
