<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="text-gray-900 antialiased leading-tight">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Cards Against COVID-19') }}</title>

	<!-- Scripts -->
	<script src="{{ asset('js/app.js') }}" defer></script>

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100">
@include("layouts.navigation")
<div id="app" class="min-h-screen pt-12 flex items-center justify-center flex-col">
	<h1 class="text-4xl mb-4">Cards Against COVID-19</h1>
	<p class="">Un jeu confiné par Eliepse et agrémenté par tous !</p>
</div>
</body>
</html>

