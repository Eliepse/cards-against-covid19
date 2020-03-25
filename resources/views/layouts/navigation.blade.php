<div class="fixed top-0 left-0 right-0 flex justify-between bg-white px-8 py-4">
	<span class="font-black text-blue-700">CAC</span>
	@auth
		<ul>
			<li><a class="text-gray-700 hover:text-gray-900 underline" href="/home">Home</a></li>
		</ul>

		<form method="POST" action="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'logout']) }}">
			@csrf
			<button type="submit" class="text-sm uppercase text-gray-700 hover:text-gray-900">Logout</button>
		</form>
	@endauth
	@guest
		<div>
			<a class="text-sm uppercase text-gray-700 hover:text-gray-900 mx-6" href="{{ route('login') }}">Login</a>
			<a class="text-sm uppercase text-gray-700 hover:text-gray-900" href="{{ route('login') }}">Register</a>
		</div>
	@endguest
</div>