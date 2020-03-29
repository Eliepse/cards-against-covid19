<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="text-gray-900 antialiased leading-tight">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'Cards Against COVID-19') }}</title>
	<link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100">

@yield('base')

@section("js")
	<script src="{{ mix('js/app.js') }}"></script>
@show

</body>
</html>
