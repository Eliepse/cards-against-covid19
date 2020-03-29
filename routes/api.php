<?php

use App\Http\Controllers\Api\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
	return $request->user();
});

Route::get('/room/{room}', [RoomController::class, 'show']);
Route::post('/room/{room}/start', [RoomController::class, 'startPlaying']);
Route::post('/room/{room}/draw', [RoomController::class, 'drawCard']);
Route::post('/room/{room}/play:white-cards', [RoomController::class, 'playWhiteCards']);
Route::post('/room/{room}/reveal:player', [RoomController::class, 'revealPlayer']);
