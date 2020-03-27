<?php
?>
@extends("layouts.app")

@section("content")
	<div id="room" class="h-screen">
		<room :user_id="{{ Auth::user()->id }}"
		      :room_id="{{ $room->id }}"
		      channel="{{ (new \App\Channels\RoomChannel($room))->getChannelId() }}"></room>
	</div>
@endsection

@section("js")
	<script src="{{ mix('js/room.js') }}"></script>
@endsection