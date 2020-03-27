<?php


namespace App\Http\Controllers;


use App\Card;
use App\Room;

class RoomController
{
	public function show(string $url)
	{
		$room = Room::query()->where('url', $url)->firstOrFail(['id', 'url']);
		$cards = Card::query()->with("contributor")->white()->inRandomOrder()->limit(5)->get();
		$blackCard = Card::query()->with("contributor")->black()->inRandomOrder()->first();
		$cards = $cards->map(function ($card) {
			$card->hovered = false;
			return $card;
		});
		return view("room.show", ["room" => $room, "cards" => $cards, "black" => $blackCard]);
	}
}