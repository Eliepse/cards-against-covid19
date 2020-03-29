<template>
	<main class="h-screen pt-12 w-full relative">

		<ul v-if="isRoomPlaying" class="text-sm text-gray-800 absolute top-0 left-0 mt-16 ml-4 font-mono">
			<li v-for="player in players.sort((a, b) => b.pivot.score - a.pivot.score)" class="mb-2"
			    :class="{'text-gray-500': !player.connected}">
				<template v-if="player.connected">
					<span v-if="isJuge(player)">&raquo;&nbsp;</span>
					<span v-else>&dash;&nbsp;</span>
				</template>
				<span v-else>&times;&nbsp;</span>
				{{ player.username }}&nbsp;: {{ player.pivot.score }}
			</li>
		</ul>

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

		<div v-if="isRoomPlaying && isRoundDrawing()" class="h-full w-full flex items-center overflow-hidden pb-20">
			<div class="fixed top-0 left-0 w-0 h-0" style="z-index: 2">
				<card v-for="(fCard,j) in fakeCards" :card='{"text":""}' :key="j"
				      :style="{top: fCard.x + 'px', left: fCard.y + 'px', transform: 'translate(-50%,-50%) rotateZ('+ fCard.r +'deg)'}"
				      class="card--small card--fake"/>
			</div>

			<div class="roomTable container m-auto flex justify-center items-center" style="z-index: 1" ref="table">
				<template v-if="isRoundDrawing('white-card')">
					<card :card="round.black_card" class="my-0 mx-0" :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}"/>
				</template>
				<template v-else-if="isRoundDrawing('black-card')">
					<div class="max-w-xs mx-auto text-center">
						<template v-if="isJuge()">
							<p class="text-gray-700 mb-8">
								Étant le maître du jeu de cette manche,
								c'est à vous de piocher la carte noire.
							</p>
							<button @click="drawBlackCard"
							        :class="{'opacity-75':starting}" :disabled="starting"
							        class="bg-gray-900 text-gray-100 hover:bg-gray-700 font-bold mx-auto
					                    py-3 px-4 rounded focus:outline-none focus:shadow-outline block mt-4">
								Piocher une carte noire
							</button>
						</template>
						<template v-else>
							<p class="text-gray-700">
								Le maître du jeu va bientôt piocher une carte...
							</p>
						</template>
					</div>
				</template>
			</div>
		</div>

		<div v-if="isRoomPlaying && round.state.startsWith('reveal:')" class="h-full w-full flex flex-col items-center">
			<div class="flex justify-center items-center mx-16 mt-8 mb-10">
				<card :card="round.black_card" :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}" class="card--small"/>
			</div>
			<div class="flex-1 flex items-center justify-center">
				<div v-for="(cards, player_id) in round.played_cards" :key="parseInt(player_id)" class="inline-block mb-6 mx-6">
					<div class="flex mb-4">
						<card v-for="(card,j) in cards" :key="j" :card="isPlayerRevealed({id:parseInt(player_id)}) ? card : {}"
						      :class="{'cursor-pointer': isJuge() && !isPlayerRevealed({id:parseInt(player_id)})}"
						      @click.native="revealPlayer({id:parseInt(player_id)})"
						      class="card--small" :style="{transform:'rotateZ('+ ((j * 8)-4) +'deg)', height: '10em', width:'9em'}"/>
					</div>
					<p v-if="round.state === 'reveal:usernames'" class="text-sm text-gray-700">
						{{ getPlayer(parseInt(player_id)).username }}
					</p>
				</div>
			</div>
		</div>

		<div class="hand w-full text-center fixed bottom-0 z-10" :class="{'hand--hide':hideHand}">
			<template v-if="isRoundDrawing('white-card')">
				<template v-if="!isJuge()">
					<button v-if="showDrawCardsBtn" @click="playSelectedCards" :disabled="played"
					        class="mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold text-center
									py-3 px-4 rounded focus:outline-none focus:shadow-outline block mb-8">
						<template v-if="selectedCards.length > 1">Jouer ces cartes</template>
						<template v-else>Jouer cette carte</template>
					</button>
					<p v-else-if="!hideHand" class="text-gray-700 mb-8">
						Sélectionnez {{ round.black_card.blanks }}
						<template v-if="round.black_card.blanks > 1">cartes dans l'ordre</template>
						<template v-else>carte</template>
						à associer avec cette carte noire.
					</p>
				</template>
				<p v-else class="text-gray-700 mb-8">
					Les autres joueurs sélectionnent leurs réponses.
				</p>
			</template>
			<template v-else-if="round.state === 'reveal:usernames' && isHost && requested !== 'newRound'">
				<button @click="newRound"
				        class="mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold text-center
									py-3 px-4 rounded focus:outline-none focus:shadow-outline block mb-8">
					Lancer la manche suivante
				</button>
			</template>
			<div class="flex justify-center mb-8">
				<Card v-for="(card,i) in hand" :key="i" :card="hideHand ? {} : card"
				      @mouseenter.native="zoomOn = card" @mouseleave.native="zoomOn = null"
				      @click.native="toggleCardSelection(card)" class="cursor-pointer card--small"
				      :class="{'card--selected border-blue-500': isCardSelected(card)}"
				      :style="{zIndex: card === zoomOn ? 20 : i+5, transform: handCardTfm(i, card)}"/>
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
		created() {
			for (let i = 0; i < 10; i++)
				this.rotations.push(Math.round(Math.random() * 12) - 6)
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
				.listen("PlayerJoinedEvent", ({players}) => {
					console.log("PlayerJoinedEvent", {players});
					this.$store.commit('addPlayers', {players})
				})
				.listen("StateChangedEvent", ({room, round}) => {
					console.log("StateChangedEvent", {room, round});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
				})
				.listen("CardsPlayedEvent", ({room, round, amount}) => {
					console.log("CardsPlayedEvent", {room, round, amount});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
					this.throwFakeCards(amount);
				})
				.listen("PlayerRevealedEvent", ({room, round, player}) => {
					console.log("PlayerRevealedEvent", {room, round, player});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
				});

			echo.private(this.private_channel)
				.listen("NewRoundEvent", ({room, round, hand}) => {
					console.log("NewRoundEvent", {room, round, hand});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
					this.$store.commit('setHand', {cards:hand});
					this.blackCardAngle = Math.round((Math.random() * 30) - 15);
					this.clearFakeCards();
					this.$forceUpdate();
				});

			if (this.$store.state.round.black_card) {
				const amount = this.$store.state.round.black_card.blanks;
				this.throwFakeCards(this.$store.state.round.played_ids.length * amount);
			}
		},
		data() {
			return {
				zoomOn: null,
				selectedCards: [],
				fakeCards: [],
				played: false,
				blackCardAngle: Math.round((Math.random() * 30) - 15),
				starting: false,
				rotations: [],
				requested: null,
			}
		},
		methods: {
			toggleCardSelection(card) {
				if (this.hideHand && this.played) return;

				const i = this.selectedCards.findIndex((c) => c.id === card.id);
				if (i >= 0) {
					// Remove selected card
					this.selectedCards.splice(i, 1);
				} else if (this.selectedCards.length < this.$store.state.round.black_card.blanks) {
					// If there is enough card
					// Append selected card
					this.selectedCards.push(card)
				}
				this.$forceUpdate();
			},
			playSelectedCards() {
				if (this.played) return;
				// Check if enough cards has been selected
				if (this.selectedCards.length !== this.$store.state.round.black_card.blanks) return;

				this.played = true;

				this.$store.dispatch("playCards", {cards: this.selectedCards})
					.then(res => {
						if (res === true) {

						} else {
							setTimeout(() => this.played = false, 500);
						}
					})
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
			},
			drawBlackCard() {
				this.$store.dispatch("drawCard", {type: 'black', amount: 1});
			},
			handCardTfm(index, card) {
				const pref = this.zoomOn === card && !this.hideHand ? 'scale(1.3) translateY(-15%)' : 'scale(1) translateY(0%)';
				return `${pref} rotateZ(${this.rotations[index]}deg)`;
			},
			isCardSelected(card) {
				return this.selectedCards.find((c) => c.id === card.id);
			},
			revealPlayer({id}) {
				if (!this.$store.getters.isJuge()) return;
				if (this.$store.getters.isPlayerRevealed({id})) return;
				this.$store.dispatch("revealPlayer", {id});
			},
			newRound() {
				if (this.requested) return;
				this.requested = 'newRound';
				this.$store.dispatch("newRound")
					.then(res => {
						if (res !== true) {
							this.requested = null;
						}
					})
			}
		},
		computed: {
			hideHand: function () {
				return this.$store.getters.hasPlayed()
					|| !this.$store.getters.isRoundDrawing("white-card")
					|| this.$store.getters.isJuge();
			},
			showDrawCardsBtn: function () {
				return !this.hideHand
					&& this.$store.getters.isRoundDrawing("white-card")
					&& this.selectedCards.length === this.$store.state.round.black_card.blanks;
			},
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
				'isRoomPlaying',
				'isRoundDrawing',
				'hasPlayed',
				'isPlayerRevealed',
				'getPlayer'
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


	.hand {
		transform: translateY(0em);
		transition: transform 250ms ease-out;
	}


	.hand--hide {
		transform: translateY(10em);
	}
</style>