<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Card
 *
 * @package App
 * @property-read int $id
 * @property string $text
 * @property-read int $blanks
 * @property-read User|null $contributor
 */
class Card extends Model
{
	const BLACK = "black";
	const WHITE = "white";

	protected $guarded = ["blanks",];


	protected function setTextAttribute(string $value): void
	{
		$this->attributes["text"] = $value;
		$this->attributes["blanks"] = preg_match_all("/[^_]?_{2,}[^_]?/", $value) ?? 0;
	}


	public function contributor(): BelongsTo
	{
		return $this->belongsTo(User::class, "contributor_id");
	}


	public function isAnswer(): bool { return $this->blanks === 0; }


	public function isQuestion(): bool { return !$this->isAnswer(); }


	public function scopeWhite(Builder $query): Builder
	{
		return $query->where("blanks", 0);
	}


	public function scopeBlack(Builder $query): Builder
	{
		return $query->where("blanks", ">", 0);
	}
}
