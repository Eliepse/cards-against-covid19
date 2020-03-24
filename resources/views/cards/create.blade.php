<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection $cards
 * @var \App\Card $card
 */
?>
@extends("layouts.app")

@section("content")
	<card-form :userid="{!! auth()->user()->id !!}"></card-form>
@endsection