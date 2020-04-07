<?php

namespace App;

use App\Cache\RoomDump;
use App\Cache\RoomHands;
use App\Cache\RoomRound;
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
 * @property int $winner_at
 * Relations
 * @property User $host
 * @property User|null $juge
 * @property Collection $players
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read RoomRound $round
 * @property-read RoomHands $hands
 * @property-read RoomDump $dump
 */
class Room extends Model
{
	public const STATE_WAITING = 'waiting';
	public const STATE_PLAYING = 'playing';
	public const STATE_TERMINATED = 'terminated';

	protected $guarded = [];
	protected $with = ['host', 'players', 'juge'];
	protected $casts = ['players_order' => 'array'];

	private array $cacheModels = [];


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

		// TODO: remove the use of collection
		$order = collect($this->players_order);
		// TODO: test if transform is useful or not
		$order->transform(fn($id) => intval($id));

		if (!$this->juge) {
			$juge = $this->players->find($order->first());
		} else {
			$index = $order->search($this->juge->id);
			$juge = $this->players->find($order->get($index + 1, $order->first()));
		}

		$this->juge()->associate($juge);

		return $this->juge;
	}


	/** @noinspection PhpUnused */
	public function getRoundAttribute(): RoomRound
	{
		if (empty($this->cacheModels['round']))
			$this->cacheModels['round'] = RoomRound::find($this);
		return $this->cacheModels['round'];
	}


	/** @noinspection PhpUnused */
	public function getHandsAttribute(): RoomHands
	{
		if (empty($this->cacheModels['hands']))
			$this->cacheModels['hands'] = RoomHands::find($this);
		return $this->cacheModels['hands'];
	}


	/** @noinspection PhpUnused */
	public function getDumpAttribute(): RoomDump
	{
		if (empty($this->cacheModels['dump']))
			$this->cacheModels['dump'] = RoomDump::find($this);
		return $this->cacheModels['dump'];
	}


	public function isWaiting(): bool
	{
		return $this->state === self::STATE_WAITING;
	}


	public function isPlaying(): bool
	{
		return $this->state === self::STATE_PLAYING;
	}


	public function isTerminated(): bool
	{
		return $this->state === self::STATE_TERMINATED;
	}

}
