<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class Room
 *
 * @package App
 * @property-read int $id
 * @property string $url
 * @property string $state
 * @property int $host_id
 * @property array $players_order
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
	public const STATE_WAITING = 'waiting';
	public const STATE_PLAYING = 'playing';
	public const STATE_TERMINATED = 'terminated';

	protected $guarded = [];
	protected $with = ['host', 'players'];


	public function players(): BelongsToMany
	{
		return $this->belongsToMany(User::class, "room_user")
			->withPivot(["score"]);
	}


	public function host(): BelongsTo
	{
		return $this->belongsTo(User::class, "host_id");
	}


	public function generateUrl(): string
	{
		$this->url = Str::random(12);
		return $this->url;
	}


	public function isWaiting(): bool
	{
		return $this->state === self::STATE_WAITING;
	}


	public function isTerminated(): bool
	{
		return $this->state === self::STATE_TERMINATED;
	}
}
