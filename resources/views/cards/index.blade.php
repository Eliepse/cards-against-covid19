<?php
/**
 * @var \Illuminate\Database\Eloquent\Collection $cards
 * @var \App\Card $card
 */
?>
@extends("layouts.app")

@section("content")
	@foreach($cards->groupBy(function ($card)  {return $card->isAnswer();})->reverse() as $deck)
		<main class="container mx-auto flex flex-row text-lg flex-wrap py-10">

			@foreach($deck as $card)
				<div class="@if($card->isAnswer()) bg-gray-100 text-gray-900 @else bg-gray-900 text-gray-100 @endif
						w-56 h-64 m-4 border border-gray-300 rounded-lg p-5 flex flex-col justify-between shadow-lg">
					<p>{{ $card->text }}</p>
					@if($card->contributor)
						<cite class="text-gray-500 d-block mt-2 text-xs text-right italic">
							par {{ $card->contributor->username }}
						</cite>
					@endif
				</div>
			@endforeach

		</main>
	@endforeach
@endsection