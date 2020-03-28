@extends('layouts.app')

@section('content')
	<div class="container min-h-screen pt-12 m-auto flex flex-col items-center justify-center">
		<div class="w-full max-w-xs">
			<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
				<a class="bg-blue-500 hover:bg-blue-700 text-white font-bold text-center py-3 px-4 rounded
						focus:outline-none focus:shadow-outline block my-4"
				   href="{{ action([\App\Http\Controllers\CardController::class, 'create']) }}">Créateur de cartes</a>
				<a class="bg-blue-500 hover:bg-blue-700 text-white font-bold text-center py-3 px-4 rounded
						focus:outline-none focus:shadow-outline block my-4"
				   href="{{ action([\App\Http\Controllers\CardController::class, 'index']) }}">Liste des cartes</a>
				<hr class="my-4">
				<p>{{ $total }} cartes dans la base de donnée dont {{ $totalSelf }} créées par vous.</p>
			</div>
		</div>
		<div class="w-full max-w-xs">
			<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
				@can('create', \App\Room::class)
					<form method="POST" action="{{ action([\App\Http\Controllers\RoomController::class, 'store']) }}">
						@csrf
						<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold text-center
							py-3 px-4 rounded focus:outline-none focus:shadow-outline block my-4 w-full">
							Créer une partie
						</button>
					</form>
				@endcan
				@cannot('create', \App\Room::class)
					<p class="text-center text-gray-500 mb-4">
						Vous ne pouvez pas créer de partie car vous êtes probablement
						déjà dans une partie encore en cours.
					</p>
					@if($room)
						<a class="bg-green-500 hover:bg-green-700 text-white font-bold text-center py-3 px-4 rounded
						focus:outline-none focus:shadow-outline block mt-4"
						   href="{{ action([\App\Http\Controllers\RoomController::class, 'show'], $room->url) }}">
							Rejoindre ma partie en cours
						</a>
					@endif
				@endcannot
			</div>
		</div>
	</div>
@endsection
