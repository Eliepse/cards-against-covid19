<?php

use App\Room;
use App\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Room.{room}.{id}', function (User $user, $room, $id) {
	if ($user->cant('join', Room::findOrFail($room))) {
		return false;
	}
	return (int)$user->id === (int)$id;
});

Broadcast::channel('App.Room.{id}', function (User $user, $id) {
	if ($user->can('join', Room::findOrFail($id))) {
		return $user->toArray();
	}
	return false;
});