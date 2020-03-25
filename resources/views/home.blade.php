@extends('layouts.app')

@section('content')
	<div class="container mt-8 m-auto flex items-center justify-center">
		<div class="w-full max-w-xs">
			<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
				<a class="bg-blue-500 hover:bg-blue-700 text-white font-bold text-center py-3 px-4 rounded
						focus:outline-none focus:shadow-outline block my-4"
				   href="{{ action([\App\Http\Controllers\CardController::class, 'create']) }}">Créateur de cartes</a>
				<a class="bg-blue-500 hover:bg-blue-700 text-white font-bold text-center py-3 px-4 rounded
						focus:outline-none focus:shadow-outline block my-4"
				   href="{{ action([\App\Http\Controllers\CardController::class, 'index']) }}">Liste des cartes</a>
				<hr class="my-4">
				<p>{{ $total }} cartes dans la base de donnée dont {{ $total }} créées par vous.</p>
			</div>
		</div>
	</div>
@endsection
