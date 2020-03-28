<?php
/** @noinspection PhpUnused */

namespace App\Policies;

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
		if($room->isTerminated()) {
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


	public function drawBlackCard(User $user, Room $room): bool
	{
		return false;
	}


	public function drawWhiteCard(User $user, Room $room): bool
	{
		return false;
	}


	public function selectRoundWinner(User $user, Room $room): bool
	{
		return false;
	}

}
