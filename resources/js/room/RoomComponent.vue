<template>
	<main class="h-screen pt-12 w-full relative">

		<div v-if="isRoomPlaying" class="absolute top-0 left-0 mt-16 ml-4 font-mono">
			<ul class="text-sm text-gray-800">
				<li v-for="player in players.sort((a, b) => b.pivot.score - a.pivot.score)" class="mb-2"
				    :class="{'text-gray-500 italic': !player.connected}">
					<template v-if="player.connected">
						<span v-if="isJuge(player)">&raquo;&nbsp;</span>
						<span v-else>&dash;&nbsp;</span>
					</template>
					<span v-else>&times;&nbsp;</span>
					{{ player.username }}&nbsp;: {{ player.pivot.score }}
				</li>
			</ul>
		</div>

		<div v-if="isRoomWaiting" class="h-full w-full flex flex-col justify-center overflow-hidden pb-20">
			<div class="w-full max-w-xs mx-auto bg-white rounded-lg border border-gray-300 shadow-xl text-center p-8">
				<h1 class="text-lg text-blue-700">En attente de joueurs</h1>
				<hr class="my-6">
				<ul>
					<li v-for="(player, i) in players" :key="i"
					    class="text-gray-400 my-2"
					    :class="{'text-gray-700': player.connected}">
						{{ player.username }}
					</li>
				</ul>
				<p class="mt-8 text-gray-700">{{ players.length }} / {{ room.max_players }}</p>
			</div>
			<button v-if="user_id === room.host_id && players.length >= 2" @click="startRoom"
			        :class="{'opacity-75':starting}" :disabled="starting"
			        class="w-full max-w-xs mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold text-center
			     py-3 px-4 rounded focus:outline-none focus:shadow-outline block mt-4">
				Démarrer la partie
			</button>

		</div>

		<div v-if="isRoomPlaying && round.state === 'draw:white-card'" class="h-full w-full flex items-center overflow-hidden pb-20">
			<div class="fixed top-0 left-0 w-0 h-0" style="z-index: 2">
				<card v-for="(fCard,j) in fakeCards" :card='{"text":""}' :key="j"
				      :style="{top: fCard.x + 'px', left: fCard.y + 'px', transform: 'translate(-50%,-50%) rotateZ('+ fCard.r +'deg)'}"
				      class="card--small card--fake"/>
			</div>

			<div class="roomTable container m-auto flex justify-center items-center" style="z-index: 1" ref="table">
				<card :card="blackCard" class="my-0 mx-0" :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}"/>
			</div>

			<div class="hand w-full fixed bottom-0 z-10">
				<div v-if="!isJuge" class="flex justify-center mb-8">
					<Card v-for="(card,i) in hand" :key="i" :card="card"
					      @mouseenter.native="zoomOn = card"
					      @mouseleave.native="zoomOn = null"
					      @click.native="toggleCardSelection(card)"
					      class="cursor-pointer"
					      :class="{'card--small':true, 'card--zoomed':card === zoomOn, 'card--selected border-blue-500': selectedCards[card.id]}"
					      :style="{zIndex: card === zoomOn ? 20 : i+5}"/>
				</div>
			</div>
		</div>

		<div v-if="isRoomPlaying && round.state === 'reveal:cards'" class="h-full w-full flex flex-col items-center">
			<div class="flex justify-center items-center mx-16 mt-8 mb-10">
				<card :card="blackCard" :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}" class="card--small"/>
			</div>
			<div class="flex-1 text-center">
				<div v-for="(player, i) in playersSelection" :key="i" class="inline-block mb-6 mx-6">
					<div class="flex mb-4">
						<card v-for="(card,j) in player.cards" :key="j" :card="card"
						      class="card--small" :style="{transform:'rotateZ('+ ((j * 8)-4) +'deg)', height: '10em', width:'9em'}"/>
					</div>
					<p v-if="revealPlayersNames" class="text-sm text-gray-700">{{ player.username }}</p>
				</div>
			</div>
		</div>

	</main>
</template>

<script>
	import Card from '../components/CardComponent'
	import {mapGetters, mapState} from 'vuex'
	import Echo from 'laravel-echo';

	export default {
		components: {Card},
		props: {
			user_id: {
				type: Number,
				required: true
			},
			room_id: {
				type: Number,
				required: true
			},
			public_channel: {
				type: String,
				required: true
			},
			private_channel: {
				type: String,
				required: true
			}
		},
		async mounted() {
			await this.$store.dispatch('loadUser', {id: this.room_id});
			await this.$store.dispatch('loadRoom', {id: this.room_id});

			const echo = new Echo({
				broadcaster: 'socket.io',
				host: window.location.hostname + ':6001',
				namespace: 'App.Events.Room'
			});

			echo.join(this.public_channel)
				.here(players => players.forEach(player => this.$store.commit('addConnectedPlayer', {player})))
				.joining(player => this.$store.commit('addConnectedPlayer', {player}))
				.leaving(player => this.$store.commit('removeConnectedPlayer', {player}))
				.listen("PlayerJoinedEvent", ({players}) => this.$store.commit('addPlayers', {players}))
				.listen("StateChangedEvent", ({room, round}) => {
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
				});

			echo.private(this.private_channel)
				.listen("NewRoundEvent", (ev) => console.log(ev))
		},
		data() {
			return {
				zoomOn: null,
				selectedCards: {},
				fakeCards: [],
				blackCardAngle: Math.round((Math.random() * 30) - 15),
				playersSelection: [],
				revealPlayersNames: false,
				starting: false,
			}
		},
		methods: {
			toggleCardSelection(card) {
				this.selectedCards[card.id] = this.selectedCards[card.id] ? null : card;
				this.$forceUpdate();
			},
			throwFakeCards(amount) {
				const o = {x: this.$el.offsetHeight / 2, y: this.$el.offsetWidth / 2};

				for (let i = 0; i < amount; i++) {
					const radius = 175 + (Math.random() * 200);
					const angle = (Math.PI * 2) * Math.random();
					this.fakeCards.push({
						x: o.x + (Math.cos(angle) * radius),
						y: o.y + (Math.sin(angle) * radius),
						r: Math.round(Math.random() * 360)
					});
				}
			},
			clearFakeCards() {
				this.fakeCards = [];
			},
			startRoom() {
				if (this.starting) return;
				this.starting = true;
				this.$store.dispatch('startRoom')
					.then((res) => {
						if (res !== true) {
							alert(res.message);
							this.starting = false
						}
					})
			}
		},
		computed: {
			...mapState({
				room: state => state.room,
				round: state => state.round,
				hand: state => state.hand
			}),
			...mapGetters([
				'players',
				'isJuge',
				'isHost',
				'isRoomWaiting',
				'isRoomPlaying'
			])
		}
	}
</script>

<style scoped>
	.roomTable {
		min-height: 20rem;
		/*height: 50vh;*/
	}


	/*noinspection CssUnusedSymbol*/
	.card--small {
		position: relative;
		margin: 0 -1em;
		font-size: .825rem;
		width: 11em;
		height: 12.5em;
	}


	/*noinspection CssUnusedSymbol*/
	.card--selected {
		transform: scale(1) translateY(-10%);
	}


	/*noinspection CssUnusedSymbol*/
	.card--zoomed {
		transform: scale(1.25) translateY(-10%);
	}


	.card--fake {
		position: fixed;
		z-index: 3;
	}
</style>