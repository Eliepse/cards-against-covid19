<?php

namespace App\Http\Controllers\Api;

use App\Cache\RoomRound;
use App\Card;
use App\Events\Room\NewRoundEvent;
use App\Events\Room\StateChangedEvent;
use App\Http\Requests\DrawCardRequest;
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
		return [
			"room" => $room,
			"round" => $room->round,
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($request->user())),
		];
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
			throw new AuthorizationException("This room is not waiting players anymore.");
		}

		if ($room->players->count() < 2) {
			throw new AuthorizationException("This room does not have enough players.");
		}

		$room->state = Room::STATE_PLAYING;
		$room->players_order = $room->players->shuffle()->pluck('id');
		$room->changeToNextJuge();
		$room->save();

		$card_stacks = Card::query()
			->select(['id'])
			->where("blanks", 0)
			->inRandomOrder()
			->limit($room->players->count() * $room->hand_size)
			->get()
			->chunk($room->hand_size);

		$round = $room->round;
		$round->state = RoomRound::STATE_DRAW_BLACK_CARD;
		$round->save();

		$hands = $room->hands;
		$room->players->each(fn($p, $i) => $hands->setForUser($p, $card_stacks->get($i)->pluck("id")->toArray()));
		$hands->save();

		broadcast(new StateChangedEvent($room))->toOthers();
		$room->players->each(fn(User $player) => broadcast(new NewRoundEvent($room, $player)));

		return [
			"round" => $round,
			"room" => $room,
			"hand" => Card::fetchHandCardsList($hands->getForUser($request->user())),
		];
	}


	/**
	 * @param DrawCardRequest $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function drawCard(DrawCardRequest $request, Room $room)
	{
		$this->authorize('drawCard', [$room, $request->get('type')]);

		$user = $request->user();
		$round = $room->round;
		$dump = $room->dump;
		$hands = $room->hands;
		$query = Card::query()
			->select(['id', 'text', 'blanks', 'contributor_id'])
			->with(["contributor"])
			->whereNotIn('id', $room->dump->ids)
			->inRandomOrder();

		// TODO: handle no more playable cards available
		// TODO: split code to make it more readable

		switch ($request->get("type")) {
			case Card::BLACK:
				/** @var Card $card */
				$query->where("blanks", ">", 0);
				$card = $query->first();
				$dump->addCard($card)->save();
				$round->black_card_id = $card->id;
				$round->state = RoomRound::STATE_DRAW_WHITE_CARD;
				$round->save();
				$cards = [$card];
				broadcast(new StateChangedEvent($room));
				break;
			case Card::WHITE:
				$query->where("blanks", 0);
				$cards = $query->limit($request->get("amount", 1))->get();
				$dump->addCards($cards)->save();
				$userHand = $hands->getForUser($user);
				$hands->setForUser($user, [...$userHand, ...$cards->pluck("id")]);
				$hands->save();
				break;
			default:
				$cards = [];
		}


		return [
			"round" => $round,
			"hand" => $hands->getForUser($user),
			"cards" => $cards,
		];
	}
}