<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection $cards
 * @var \App\Card $card
 */
?>
@extends("layouts.app")

@section("content")
	<div class="min-h-screen pt-12">
		<card-form :userid="{!! auth()->user()->id !!}"></card-form>
	</div>
@endsection