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
			$table->foreignId("juge_id")->nullable()->constrained("users");
			$table->json("players_order")->nullable();
			$table->unsignedTinyInteger("max_players")->default(8);
			$table->unsignedTinyInteger("hand_size")->default(5);
			$table->timestamps();
//			$table->foreignId("black_card_id")->constrained("cards")->nullable(); -> in cache ?
//			$table->json("used_cards")->default([]); -> in cache ?
//			$table->json("playing_cards")->default([]); -> in cache ?
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
