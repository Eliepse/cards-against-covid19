<?php
/** @noinspection PhpUnused */

namespace App\Policies;

use App\Cache\RoomRound;
use App\Card;
use App\Room;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomPolicy
{
	use HandlesAuthorization;


	/**
	 * Determine whether the user can view any rooms.
	 *
	 * @param \App\User $user
	 *
	 * @return mixed
	 */
	public function viewAny(User $user): bool
	{
		return false;
	}


	/**
	 * Determine whether the user can view the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function view(User $user, Room $room): bool
	{
		if ($room->host->is($user)) {
			return true;
		}

		if ($room->players->firstWhere('id', $user->id)) {
			return true;
		}

		return false;
	}


	/**
	 * Determine whether the user can create rooms.
	 *
	 * @param \App\User $user
	 *
	 * @return mixed
	 */
	public function create(User $user): bool
	{
		if ($user->hostedRooms()->where('state', '!=', Room::STATE_TERMINATED)->exists()) {
			return false;
		}

		if ($user->playedRooms()->where('state', '!=', Room::STATE_TERMINATED)->exists()) {
			return false;
		}

		return true;
	}


	/**
	 * Determine whether the user can update the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function update(User $user, Room $room): bool
	{
		return false;
	}


	/**
	 * Determine whether the user can delete the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function delete(User $user, Room $room): bool
	{
		return false;
	}


	/**
	 * Determine whether the user can restore the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function restore(User $user, Room $room): bool
	{
		return false;
	}


	/**
	 * Determine whether the user can permanently delete the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function forceDelete(User $user, Room $room): bool
	{
		return false;
	}


	public function join(User $user, Room $room): bool
	{
		if ($room->isTerminated()) {
			return false;
		}

		if ($room->players->firstWhere('id', $user->id)) {
			return true;
		}

		if ($room->isWaiting() && $room->players->count() < $room->max_players) {
			return true;
		}


		return false;
	}


	public function drawCard(User $user, Room $room, string $type, int $amount = 1): bool
	{
		if (!$room->players->containsStrict('id', $user->id)) {
			return false;
		}

		if (!$room->isPlaying()) {
			return false;
		}

		switch ($type) {
			case Card::WHITE:
//				if ($room->hands->getForUser($user) === RoomRound::STATE_DRAW_WHITE_CARD) return false;
//				if (!$room->round->black_card_id) return false;
//				if (empty($room->round->players_played_cards_ids[ $user->id ])) return true;
//				$maxPlayable = $room->round->getBlackCard()->blanks;
//				return count($room->round->players_played_cards_ids[ $user->id ]) < $maxPlayable;
				return count($room->hands->getForUser($user)) + $amount <= $room->hand_size;
			case Card::BLACK:
				return $amount === 1
					&& $room->juge
					&& $room->juge->is($user)
					&& is_null($room->round->black_card_id)
					&& $room->round->state === RoomRound::STATE_DRAW_BLACK_CARD;
			default:
				return false;
		}
	}


	public function playWhiteCards(User $user, Room $room, int $amount): bool
	{
		if (!$room->players->containsStrict('id', $user->id)) return false;
		if (!$room->isPlaying()) return false;
		if ($room->juge->is($user)) return false;
		if ($room->round->state !== RoomRound::STATE_DRAW_WHITE_CARD) return false;

		return $room->round->getWhiteCardsOf($user)->count() + $amount <= $room->round->getBlackCard()->blanks;
	}


	public function selectRoundWinner(User $user, Room $room): bool
	{
		return false;
	}

}
