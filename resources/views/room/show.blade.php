<?php
?>
@extends("layouts.app")

@section("content")
	<div id="room" class="h-screen">
		<room :user_id="{{ Auth::user()->id }}"
		      :room_id="{{ $room->id }}"
		      public_channel="{{ (new \App\Channels\PresenceRoomChannel($room))->getChannelId() }}"
		      private_channel="{{ (new \App\Channels\PrivateRoomChannel($room, Auth::user()))->getChannelId() }}">
		</room>
	</div>
@endsection

@section("js")
	<script src="{{ mix('js/room.js') }}"></script>
@endsection