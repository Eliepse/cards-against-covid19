@extends('layouts.app')

@section('content')
	<div class="container h-screen m-auto flex items-center justify-center">
		<div class="w-full max-w-xs">

			<form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" method="POST" action="{{ route('register') }}">
				<h1 class="text-2xl mb-8 text-center text-blue-700">Register</h1>
				@csrf
				<div class="mb-4">
					<label class="block text-gray-700 text-sm font-bold mb-2" for="username">
						{{ __('Username') }}
					</label>
					<input class="shadow appearance-none border @error('password') border-red-500 @enderror rounded
						w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
					       required
					       autocomplete="username"
					       name="username"
					       id="username" type="text" placeholder="Username" value="{{ old('username') }}" autofocus>

					@error('username')
					<p class="text-red-500 mt-3 text-xs italic" role="alert">{{ $message }}</p>
					@enderror
				</div>
				<div class="mb-6">
					<label class="block text-gray-700 text-sm font-bold mb-2" for="password">
						{{ __('Password') }}
					</label>
					<input class="shadow appearance-none border @error('password') border-red-500 @enderror rounded
						w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
					       autocomplete="current-password"
					       name="password"
					       required
					       id="password" type="password" placeholder="******************">
					@error('password')
					<p class="text-red-500 mt-3 text-xs italic" role="alert">{{ $message }}</p>
					@enderror
				</div>
				<div class="flex items-center justify-between">
					<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded
						focus:outline-none focus:shadow-outline" type="submit">
						{{ __('Register') }}
					</button>
				</div>
			</form>
			<p class="text-center text-gray-500 text-xs">
			</p>
		</div>
	</div>
@endsection
