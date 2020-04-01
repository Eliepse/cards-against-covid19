<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'welcome');

Route::get('login', [LoginController::class, 'showLoginForm'])
	->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])
	->name('logout');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])
	->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/cards', [CardController::class, 'index']);
Route::get('/cards/new', [CardController::class, 'create']);
Route::post('/cards', [CardController::class, 'store']);

Route::get('/room/{room}', [RoomController::class, 'show']);
Route::delete('/room/{room}', [RoomController::class, 'terminate']);
Route::post('/room/{room}/leave', [RoomController::class, 'leave']);
Route::post('/room', [RoomController::class, 'store']);
