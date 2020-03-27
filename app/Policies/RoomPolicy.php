<?php

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
	public function viewAny(User $user)
	{
		//
	}


	/**
	 * Determine whether the user can view the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function view(User $user, Room $room)
	{
		//
	}


	/**
	 * Determine whether the user can create rooms.
	 *
	 * @param \App\User $user
	 *
	 * @return mixed
	 */
	public function create(User $user)
	{
		//
	}


	/**
	 * Determine whether the user can update the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function update(User $user, Room $room)
	{
		//
	}


	/**
	 * Determine whether the user can delete the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function delete(User $user, Room $room)
	{
		//
	}


	/**
	 * Determine whether the user can restore the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function restore(User $user, Room $room)
	{
		//
	}


	/**
	 * Determine whether the user can permanently delete the room.
	 *
	 * @param \App\User $user
	 * @param \App\Room $room
	 *
	 * @return mixed
	 */
	public function forceDelete(User $user, Room $room)
	{
		//
	}


	public function drawBlackCard(User $user, Room $room)
	{
		//
	}


	public function drawWhiteCard(User $user, Room $room)
	{
		//
	}


	public function selectRoundWinner(User $user, Room $room)
	{
		//
	}


	public function forceChangeState(User $user, Room $room)
	{
		// handle force starting the game
		// handle force new round ?
		// handle force terminate the game -> scores page
	}
}
