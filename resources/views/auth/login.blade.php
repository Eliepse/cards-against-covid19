<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Cards Against COVID-19') }}</title>

	<link href="{{ asset('css/public.css') }}" rel="stylesheet">
</head>
<body class="public public--login">
<div class="card card--light">
	<h1 class="card__title">Connexion</h1>
	<form method="POST" action="{{ route('login') }}">
		@csrf
		<div class="card__field @error('username') card__field--error @enderror">
			<label for="username">Surnom</label>
			<input required autocomplete="username" name="username" id="username" type="text" placeholder="Username" value="{{ old('username') }}" autofocus>
			@error('username')
			<p class="card__field-message" role="alert">{{ $message }}</p>
			@enderror
		</div>
		<div class="card__field @error('password') card__field--error @enderror">
			<label for="password">Mot de passe</label>
			<input autocomplete="current-password" name="password" required id="password" type="password" placeholder="******************">
			@error('password')
			<p class="card__field-message" role="alert">{{ $message }}</p>
			@enderror
		</div>
		<div class="card__field">
			<input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
			<label for="remember">Garder ma session ouverte</label>
		</div>
		<button class="btn btn--black card__btn--tilted" type="submit">Se connecter</button>
	</form>
</div>
<a class="btn card__btn--register" href="{{ route('register') }}">Créer un compte</a>
</body>
</html>