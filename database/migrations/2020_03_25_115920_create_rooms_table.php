<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rooms', function (Blueprint $table) {
			$table->id();
			$table->string("url")->unique();
			$table->string("state")->default("waiting"); // waiting, playing, terminated
			$table->foreignId("host_id")->constrained("users");
			$table->json("player_order")->default(json_encode([]));
			$table->unsignedTinyInteger("max_players")->default(8);
			$table->unsignedTinyInteger("hand_size")->default(5);
			$table->timestamps();
//			$table->foreignId("black_card_id")->constrained("cards")->nullable(); -> in cache ?
//			$table->json("used_cards")->default([]); -> in cache ?
//			$table->json("playing_cards")->default([]); -> in cache ?
//			$table->foreignId("juge_id")->constrained("users")->nullable(); -> in cache ?
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('rooms');
	}
}
