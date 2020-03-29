@extends('layouts.empty')

@section('base')
	@include("layouts.navigation")
	<div id="app" class="">
		@yield('content')
	</div>
@endsection