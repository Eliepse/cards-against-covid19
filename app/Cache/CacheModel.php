<?php


namespace App\Cache;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

abstract class CacheModel implements Arrayable, \JsonSerializable
{
	public const CACHE_TIMEMOUT = 24 * 3_600;

	/**
	 * Id of the round to retreive it from the cache
	 *
	 * @var string
	 */
	private string $id;


	public function __construct(string $relatedClassname, int $relatedId, string $key)
	{
		$this->id = "App.{$relatedClassname}.{$relatedId}:$key";
	}


	abstract function refresh(): self;


	protected function fetchRawAttributes(): array
	{
		return Cache::get($this->id, []);
	}


	public function save(): self
	{
		Cache::put(
			$this->id,
			$this->toSaveArray(),
			self::CACHE_TIMEMOUT
		);

		return $this;
	}


	public function toSaveArray(): array
	{
		return $this->toArray();
	}


	public function jsonSerialize()
	{
		return $this->toArray();
	}
}