<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cards', function (Blueprint $table) {
			$table->id();
			$table->string("text");
			$table->unsignedTinyInteger("blanks");
			$table->unsignedBigInteger("contributor_id")->nullable();
			$table->timestamps();

			$table->foreign("contributor_id")
				->references("id")
				->on('users')
				->onUpdate("cascade")
				->onDelete("set null");
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cards');
	}
}
