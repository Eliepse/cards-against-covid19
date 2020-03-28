<?php

namespace App\Http\Controllers;

use App\Card;
use App\Room;
use App\User;
use Illuminate\Http\Request;

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
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index()
	{
		/** @var User $user */
		$user = auth()->user();

		$room = $user->playedRooms()
			->where("state", "!=", Room::STATE_TERMINATED)
			->first(['id', 'url']);

		return view('home', [
			"user" => $user,
			"room" => $room,
			"total" => Card::query()->count(),
			"totalSelf" => $user->cards()->count(),
		]);
	}
}
