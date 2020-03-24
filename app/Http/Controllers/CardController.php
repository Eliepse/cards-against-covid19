<?php

namespace App\Http\Controllers;

use App\Card;
use App\Http\Requests\StoreCardRequest;
use App\User;
use Error;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CardController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Factory|View
	 */
	public function index()
	{
		$cards = Card::with("contributor:id,username")->get();
		return view("cards.index", ["cards" => $cards]);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Factory|View
	 */
	public function create()
	{
		/** @var User $user */
		$user = auth()->user();
		$cards = $user->cards()
			->orderBy("created_at", "desc")
			->select(["id", "text", "blanks", "contributor_id"])
			->limit(5)
			->get();

		return view("cards.create", ["cards" => $cards]);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function store(StoreCardRequest $request)
	{
		/** @var User $user */
		$user = auth()->user();
		$card = new Card($request->all(["text"]));

		if (!$user->cards()->save($card)) {
			throw new Error("Error while saving the card in the database.");
		}

		return response()->json(["card" => [
			"text" => $card->text,
			"contributor" => $card->contributor->username,
		]]);
	}
}
