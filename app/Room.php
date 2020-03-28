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
 * @property User|null $juge
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
	protected $with = ['host', 'players', 'juge'];


	public function players(): BelongsToMany
	{
		return $this->belongsToMany(User::class, "room_user")
			->withPivot(["score"]);
	}


	public function host(): BelongsTo
	{
		return $this->belongsTo(User::class, "host_id");
	}


	public function juge(): BelongsTo
	{
		return $this->belongsTo(User::class, 'juge_id');
	}


	public function generateUrl(): string
	{
		$this->url = Str::random(12);
		return $this->url;
	}


	public function changeToNextJuge(): ?User
	{
		if (count($this->players_order) === 0) {
			return null;
		}

		$index = $this->players->search(fn($player) => $player->is($this->juge)) || 0;
		$juge = $this->players->get($index + 1, 0);
		$this->juge()->associate($juge);

		return $this->juge;
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
