<?php

namespace Tests\Unit;

use App\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
	/** @test */
	public function set_text_without_blank()
	{
		$card = new Card(["text" => "John Doe"]);
		$this->assertEquals(0, $card->blanks);
	}


	/** @test */
	public function set_text_with_blanks()
	{
		$card = new Card(["text" => "Only __ can save the world."]);
		$this->assertEquals(1, $card->blanks);

		$card = new Card(["text" => "Only _______ can _ save the world."]);
		$this->assertEquals(1, $card->blanks);

		$card->text = "__ is obsessed with __ an __.";
		$this->assertEquals(3, $card->blanks);

		$card->text = "__ is obsessed_ with ______ an __.";
		$this->assertEquals(3, $card->blanks);
	}
}
