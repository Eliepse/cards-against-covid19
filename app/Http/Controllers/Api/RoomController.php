<?php

namespace App\Http\Controllers\Api;

use App\Cache\RoomRound;
use App\Card;
use App\Events\Room\CardsPlayedEvent;
use App\Events\Room\NewRoundEvent;
use App\Events\Room\PlayerRevealedEvent;
use App\Events\Room\StateChangedEvent;
use App\Http\Requests\DrawCardRequest;
use App\Http\Requests\PlayWhiteCardsRequest;
use App\Http\Requests\RevealPlayerRequest;
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
		$data = [
			"room" => $room,
			"round" => $room->round,
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($request->user())),
		];

		if ($room->juge && $room->juge->is($request->user())) {
			$data["round"] = array_merge(
				$room->round->toArray(),
				["played_cards" => $room->round->getWhiteCards()]
			);
		}

		return $data;
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


	/**
	 * @param PlayWhiteCardsRequest $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function playWhiteCards(PlayWhiteCardsRequest $request, Room $room)
	{
		$card_ids = $request->get('cards');
		$amount = count($card_ids);
		$this->authorize('playWhiteCards', [$room, $amount]);
		$user = $request->user();

		// Check if enough card are played
		$needed = $room->round->getBlackCard()->blanks;
		if ($amount !== $needed) {
			throw new AuthorizationException("You should play the right amount of cards ($needed for this round).");
		}

		// Check if cards have been dumped
		if (!empty(array_intersect($room->dump->ids, $card_ids))) {
			throw new AuthorizationException("You cannot play cards that have already been dumped.");
		}

		// Check if cards are in hand
		$valid_cards = array_values(array_intersect($room->hands->getForUser($user), $card_ids));
		if (count($valid_cards) !== $amount) {
			throw new AuthorizationException("You cannot play cards that are not part of your hand.");
		}

		// Add to played cards
		$room->round->addPlayedCardBy($user, $card_ids);
		$room->round->played_ids = array_merge($room->round->played_ids, [$user->id]);
		$room->round->save();

		// Remove card from hand
		$room->hands
			->removeFromUser($user, $card_ids)
			->save();

		// Broadcast event
		broadcast(new CardsPlayedEvent($room, count($card_ids)));

		// Check if everyone played
		// The number of players does not include the juge here
		if (count($room->round->played_ids) === $room->players->count() - 1) {
			$room->round->state = RoomRound::STATE_REVEAL_CARDS;
			$room->round->save();
			broadcast(new StateChangedEvent($room));
		}

		return [
			"room" => $room,
			"round" => $room->round,
			"hand" => $room->hands->getForUser($user),
		];
	}


	/**
	 * @param RevealPlayerRequest $request
	 * @param Room $room
	 *
	 * @throws AuthorizationException
	 */
	public function revealPlayer(RevealPlayerRequest $request, Room $room)
	{
		/** @var User $player */
		$player = User::query()->find($request->get("player_id"));
		$this->authorize("revealPlayer", [$room, $player]);

		$room->round->revealed_ids[] = $player->id;
		if (count($room->round->revealed_ids) === $room->players->count() - 1) {
			$room->round->state = RoomRound::STATE_REVEAL_USERNAMES;
		}
		$room->round->save();

		broadcast(new PlayerRevealedEvent($room, $player))->toOthers();

		return [
			"room" => $room,
			"round" => $room->round,
			"player" => $player,
		];
	}
}