<?php

namespace Tests\Feature\Api;

use App\Room;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
	use DatabaseMigrations;


	/**
	 * @test
	 */
	public function get_room_as_host_succeed()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		/** @var Room $room */
		$room = factory(Room::class)->create(['host_id' => $user->id]);
		$room->loadMissing(["host", "players"])->refresh();

		$res = $this->getJson("/api/room/{$room->id}");

		$res->assertOk();
		$this->assertEquals($room->toArray(), $res->json("room"));
		$this->assertNull($res->json("black_card"));
	}


	/**
	 * @test
	 */
	public function get_room_as_player_succeed()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		/** @var Room $room */
		$room = factory(Room::class)->create();
		$room->players()->attach($user);
		$room->loadMissing(["host", "players"])->refresh();

		$res = $this->getJson("/api/room/{$room->id}");

		$res->assertOk();
		$this->assertEquals($room->toArray(), $res->json("room"));
		$this->assertNull($res->json("black_card"));
	}


	/**
	 * @test
	 */
	public function get_room_unauthorized()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		$room = factory(Room::class)->create();
		$room->refresh();

		$res = $this->getJson("/api/room/{$room->id}");

		$res->assertForbidden();
	}


	/**
	 * @test
	 */
	public function create_room_succeed()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);

		$res = $this->postJson("/api/room");

		$res->assertSuccessful();
	}


	/**
	 * @test
	 */
	public function create_room_while_hosting_unauthorized()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		factory(Room::class)->create([
			'host_id' => $user->id,
			'state' => Room::STATE_PLAYING,
		]);

		$res = $this->postJson("/api/room");

		$res->assertForbidden();
	}


	/**
	 * @test
	 */
	public function create_room_while_playing_unauthorized()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		$room = factory(Room::class)->create(['state' => Room::STATE_PLAYING,]);
		$room->players()->attach($user);

		$res = $this->postJson("/api/room");

		$res->assertForbidden();
	}


	/**
	 * @test
	 */
	public function create_room_after_hosting_or_playing_succeed()
	{
		$user = factory(User::class)->create();
		Passport::actingAs($user, ['check-status']);
		factory(Room::class)->create([
			'host_id' => $user->id,
			'state' => Room::STATE_TERMINATED,
		]);
		factory(Room::class)
			->create(['state' => Room::STATE_TERMINATED])
			->players()->attach($user);

		$res = $this->postJson("/api/room");

		$res->assertSuccessful();
	}
}
