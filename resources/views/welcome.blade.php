<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Cards Against COVID-19') }}</title>

	<link href="{{ asset('css/public.css') }}" rel="stylesheet">
</head>
<body class="public public--welcome">
<div class="welcome__center">
	<h1 class="welcome__title">
		Cards<br>
		Against<br>
		COVID-19
	</h1>
	<a class="welcome__playBtn" href="{{ route('home') }}">Jouer&nbsp;→</a>
</div>
<div class="welcome__notes">
	<p>
		Un jeu confiné par Eliepse et agrémenté par tous !<br>
		Ce jeu est basé sur
		<cite>
			<a href="https://www.cardsagainsthumanity.com" rel="noreferrer">
				Cards Against Humanity.
			</a>
		</cite>
	</p>
</div>
</body>
</html>

