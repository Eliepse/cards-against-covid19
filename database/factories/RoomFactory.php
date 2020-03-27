<?php

/** @var Factory $factory */

use App\Room;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

$factory->define(Room::class, function (Faker $faker) {
	return [
		'url' => Str::random(12),
		'state' => Arr::random([Room::STATE_WAITING, Room::STATE_PLAYING, Room::STATE_TERMINATED,]),
		'max_players' => $faker->numberBetween(3, 8),
		'hand_size' => $faker->numberBetween(5, 8),
		'host_id' => function (): int {
			return factory(User::class)->create()->id;
		},
	];
});
