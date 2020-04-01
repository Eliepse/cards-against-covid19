<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @package App
 * @property-read int $id
 * @property string $username
 * @property string $password
 * // Relations
 * @property Collection $cards
 * @property Collection $hostedRooms
 * @property Collection $playedRooms
 * @property \stdClass $pivot
 */
class User extends Authenticatable
{
	use Notifiable, HasApiTokens;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['username', 'password',];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at',
		'updated_at',
		'password',
		'remember_token',
	];


	public function cards(): HasMany
	{
		return $this->hasMany(Card::class, "contributor_id");
	}


	public function hostedRooms(): HasMany
	{
		return $this->hasMany(Room::class, 'host_id');
	}


	public function playedRooms(): BelongsToMany
	{
		return $this->belongsToMany(Room::class);
	}
}
