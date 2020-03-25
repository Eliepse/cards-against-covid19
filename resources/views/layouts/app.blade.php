<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="text-gray-900 antialiased leading-tight">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Laravel') }}</title>

	<!-- Scripts -->
	<script src="{{ asset('js/app.js') }}" defer></script>

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100">
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
</div>
<div id="app" class="min-h-screen pt-12">
	@yield('content')
</div>
</body>
</html>
