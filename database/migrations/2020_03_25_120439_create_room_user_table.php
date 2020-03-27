<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomUserTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('room_user', function (Blueprint $table) {
			$table->foreignId("room_id")->constrained("rooms");
			$table->foreignId("user_id")->constrained("users");
			$table->smallInteger("score")->default(0);
//			$table->json("hand_cards")->default([]); -> in cache ?
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('room_user');
	}
}
