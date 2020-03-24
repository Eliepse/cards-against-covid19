<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection $cards
 * @var \App\Card $card
 */
?>
@extends("layouts.app")

@section("content")
	{{--	<card-form></card-form>--}}
	<card-form :lastcreated='@json($cards->map(function ($card)  {return ["text" => $card->text, "contributor" => optional($card->contributor)->username];}))'>

	</card-form>
@endsection