<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Class Room
 *
 * @package App
 * @property-read int $id
 * @property string $url
 * @property string $state
 * @property int $host_id
 * @property array $player_order
 * @property int $max_players
 * @property int $hand_size
 * Relations
 * @property User $host
 * @property Collection $players
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Room extends Model
{
	protected $guarded = [];


	public function players(): BelongsToMany
	{
		return $this->belongsToMany(User::class, "room_user")
			->withPivot(["score"]);
	}
	

	public function host(): BelongsTo
	{
		return $this->belongsTo(User::class, "host_id");
	}
}
