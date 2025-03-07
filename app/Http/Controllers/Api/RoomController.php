<?php

namespace App\Http\Controllers\Api;

use App\Cache\RoomRound;
use App\Card;
use App\Events\PlayerWonRoundEvent;
use App\Events\Room\CardsPlayedEvent;
use App\Events\Room\NewRoundEvent;
use App\Events\Room\PlayerRevealedEvent;
use App\Events\Room\StateChangedEvent;
use App\Http\Requests\DrawCardRequest;
use App\Http\Requests\PlayWhiteCardsRequest;
use App\Http\Requests\RevealPlayerRequest;
use App\Http\Requests\SelectRoundWinnerRequest;
use App\Room;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
		// TODO: Check if the person have the RIGHT to do before checking if it's possible to do it

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

		$cards = Card::query()
			->select(['id'])
			->where("blanks", 0)
			->inRandomOrder()
			->limit($room->players->count() * $room->hand_size)
			->get();

		$card_stacks = $cards->chunk($room->hand_size);
		$room->dump->addCards($cards);
		$room->dump->save();

		$room->round->state = RoomRound::STATE_DRAW_BLACK_CARD;
		$room->round->save();

		$room->players->each(fn($p, $i) => $room->hands->setForUser($p, $card_stacks->get($i)->pluck("id")->toArray()));
		$room->hands->save();

		broadcast(new StateChangedEvent($room))->toOthers();
		$room->players->each(fn(User $player) => broadcast(new NewRoundEvent($room, $player)));

		return [
			"round" => $room->round,
			"room" => $room,
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($request->user())),
		];
	}


	/**
	 * @param DrawCardRequest $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function drawBlackCard(Request $request, Room $room)
	{
		$user = $request->user();
		/** @var Card $card */
		$card = Card::query()
			->select(['id', 'text', 'blanks', 'contributor_id'])
			->with(["contributor"])
			->whereNotIn('id', $room->dump->ids)
			->inRandomOrder()
			->where("blanks", ">", 0)
			->first();

		// TODO: handle no more playable cards available
		// TODO: split code to make it more readable

		$room->dump->addCard($card)->save();
		$room->round->black_card_id = $card->id;
		$room->round->state = RoomRound::STATE_DRAW_WHITE_CARD;
		$room->round->save();

		broadcast(new StateChangedEvent($room));

		return [
			"round" => $room->round,
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($user)),
			"cards" => [$card],
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
			Log::debug("You should play the right amount of cards ($needed for this round).", [
				"card_ids" => $card_ids,
				"amount" => $amount,
				"needed_amount" => $needed,
			]);
			throw new AuthorizationException("You should play the right amount of cards ($needed for this round).");
		}

		// Check if cards are in hand
		$valid_cards = array_unique(array_values(array_intersect($room->hands->getForUser($user), $card_ids)));
		if (count($valid_cards) !== $amount) {
			Log::debug("You cannot play cards that are not part of your hand.", [
				"card_ids" => $card_ids,
				"valid_cards" => $valid_cards,
				"user_hand" => $room->hands->getForUser($user),
				"needed_amount" => $needed,
			]);
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
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($user)),
		];
	}


	/**
	 * @param RevealPlayerRequest $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function revealPlayer(RevealPlayerRequest $request, Room $room)
	{
		/** @var User $player */
		$player = User::query()->find($request->get("player_id"));
		$this->authorize("revealPlayer", [$room, $player]);

		$room->round->revealed_ids[] = $player->id;
		if (count($room->round->revealed_ids) === $room->players->count() - 1) {
			$room->round->state = RoomRound::STATE_SELECT_WINNER;
		}
		$room->round->save();

		broadcast(new PlayerRevealedEvent($room, $player))->toOthers();

		return [
			"room" => $room,
			"round" => $room->round,
			"player" => $player,
		];
	}


	/**
	 * @param Request $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function newRound(Request $request, Room $room)
	{
		$this->authorize("newRound", $room);

		$room->changeToNextJuge();
		$room->save();

		$room->round->reset()->save();
		$totalHand = collect($room->hands->hands)->flatten();
		$expectedCount = $room->hand_size * $room->players->count();

		$newCards = Card::query()
			->select(["id"])
			->whereNotIn("id", $room->dump->ids)
			->where("blanks", 0)
			->inRandomOrder()
			->limit($expectedCount - $totalHand->count())
			->get();

		foreach ($room->players as $player) {
			$missingCards = $room->hand_size - count($room->hands->getForUser($player));
			$cards = $newCards->splice(0, $missingCards);
			$room->hands->addToUser($player, $cards->pluck("id")->toArray());
			$room->dump->addCards($cards);
		}

		$room->hands->save();
		$room->dump->save();

		foreach ($room->players as $player) {
			broadcast(new NewRoundEvent($room, $player));
		}

		return [
			"round" => $room->round,
			"room" => $room,
			"hand" => Card::fetchHandCardsList($room->hands->getForUser($request->user())),
		];
	}


	/**
	 * @param SelectRoundWinnerRequest $request
	 * @param Room $room
	 *
	 * @return array
	 * @throws AuthorizationException
	 */
	public function selectRoundWinner(SelectRoundWinnerRequest $request, Room $room)
	{
		$this->authorize("selectWinner", $room);
		/** @var User $player */
		$player = $room->players->find($request->get('player_id'));

		if (!$player) {
			throw new AuthorizationException("You cannot choose a player that is not part of this room.");
		}

		if ($room->juge->is($player)) {
			throw new AuthorizationException("You cannot choose yourself as a winner.");
		}

		$room->players()->updateExistingPivot($player, ["score" => $player->pivot->score + 1], true);

		$room->round->winner_id = $player->id;
		$room->round->state = RoomRound::STATE_COMPLETED;
		$room->round->save();

		// If a player won the game (not just the round)
		if ($room->players->first(fn(User $player) => $player->pivot->score >= $room->winner_at)) {
			$room->state = Room::STATE_TERMINATED;
			$room->save();
		} else {
			broadcast(new PlayerWonRoundEvent($room, $player));
		}

		broadcast(new StateChangedEvent($room));

		return [
			"room" => $room,
			"round" => $room->round,
			"player" => $player,
		];
	}
}