<div class="fixed top-0 left-0 right-0 flex justify-between bg-white px-8 py-4 z-50">
	<span class="font-black text-blue-700">CAC</span>
	@auth
		<ul>
			<li><a class="text-gray-700 hover:text-gray-900 underline" href="/home">Home</a></li>
		</ul>

		@can("terminate", $room)
			<form method="POST" action="{{ action([\App\Http\Controllers\RoomController::class, 'terminate'], $room) }}">
				@csrf
				@method('DELETE')
				<button type="submit" class="text-sm uppercase text-gray-700 hover:text-gray-900">Arrêter la partie</button>
			</form>
		@endcan
	@endauth
</div>