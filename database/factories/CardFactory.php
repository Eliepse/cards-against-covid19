<?php

/** @var Factory $factory */

use App\Card;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Card::class, function (Faker $faker) {

	$card = new Card();

	if (random_int(1, 3) === 1)
		$card->text = $faker->sentence(4) . "_____" . ($faker->boolean ? $faker->sentence(4) . "_____" : "");
	else
		$card->text = $faker->sentence;

	return [
		"text" => $card->text,
		"blanks" => $card->blanks,
		"contributor_id" => User::query()->inRandomOrder()->first()->id,
	];
});
