<template>
	<main class="h-screen pt-12 w-full relative">

		<template v-if="room.state !== 'terminated'">
			<!-- Leaderboard -->
			<ul v-if="isRoomPlaying" class="text-sm text-gray-800 absolute top-0 left-0 mt-16 ml-4 font-mono">
				<li v-for="id in room.players_order" class="mb-2"
				    :class="{'text-gray-500': !getPlayer(id).connected}">
					<template v-if="getPlayer(id).connected">
						<span v-if="isJuge(getPlayer(id))">&raquo;&nbsp;</span>
						<span v-else>&dash;&nbsp;</span>
					</template>
					<span v-else>&times;&nbsp;</span>
					{{ getPlayer(id).username }}
				</li>
			</ul>

			<!-- Fake cards -->
			<transition-group tag="div" class="fixed top-0 left-0 w-0 h-0" style="z-index: 2" :css="false"
			                  @before-enter="fakeCardBeforeEnter" @enter="fakeCardEnter" @leave="fakeCardLeave">
				<card v-for="j in playedCardsCount" :card='{}' :key="j" size="xs" style="position: fixed; z-index: 3;"/>
			</transition-group>

			<!-- Waiting screen -->
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
				<btn v-if="isHost && players.length >= 2" @click.native="startRoom" class="mt-4" :disabled="loading">
					Démarrer la partie
				</btn>
			</div>

			<!-- Playing screen -->
			<template v-if="isRoomPlaying">
				<div v-if="isRoundDrawing()" class="h-full w-full flex items-center overflow-hidden pb-20">
					<!-- Table (where the black card is) -->
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
									<btn @click.native="drawBlackCard" class="bt-4" color="black" :disabled="loading">
										Piocher une carte noire
									</btn>
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
				<div v-else class="h-full w-full flex items-center overflow-hidden pb-20">
					<div class="roomTable container m-auto flex flex-col justify-center items-center" style="z-index: 1">
						<div class="flex justify-center items-center mx-16 mt-8 mb-10">
							<p class="text-gray-700" v-if="round.state === 'completed'">
								Le gagnant de cette manche est
								<strong>{{ round.winner_id ? getPlayer(round.winner_id).username : '' }} !</strong>
							</p>
							<card v-else :card="round.black_card"
							      :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}" size="xs"/>
						</div>
						<div class="flex-1 flex items-center justify-center">
							<div v-for="(cards, player_id) in round.played_cards" :key="parseInt(player_id)"
							     class="inline-block mb-6 mx-6">
								<div v-if="round.state.startsWith('reveal:')" class="flex mb-4">
									<card v-for="(card,j) in cards" :key="j" :card="getRevealedCard(card, parseInt(player_id))"
									      :class="{'cursor-pointer': isJuge() && !isPlayerRevealed({id:parseInt(player_id)})}"
									      @click.native="revealPlayer({id:parseInt(player_id)})"
									      size="sm" :style="{transform:'rotateZ('+ ((j * 8)-4) +'deg)'}"/>
								</div>
								<div v-else class="flex mb-4"
								     @mouseenter="hoveredPlayer = isJuge() && round.state === 'select:winner' ? player_id : null"
								     @mouseleave="hoveredPlayer = null"
								>
									<card v-for="(card,j) in cards" :key="j"
									      :card="getRevealedCard(card, parseInt(player_id))"
									      :class="{
									      	'cursor-pointer': isJuge() && round.state === 'select:winner',
									        'border-2 border-blue-500':hoveredPlayer === player_id || parseInt(player_id) === round.winner_id
									      }"
									      @click.native="selectWinner({player_id:parseInt(player_id)})"
									      size="sm" :style="{transform:'rotateZ('+ ((j * 8)-4) +'deg)'}"/>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>

			<hand class="fixed bottom-0 z-10" ref="hand" :cards="hand" :needed="neededWhiteCards">
				<template v-if="isRoundDrawing('white-card')">
					<p v-if="!isJuge() && !hasPlayed()" class="text-gray-700 mb-8">
						Sélectionnez {{ neededWhiteCards }}
						<template v-if="neededWhiteCards > 1">cartes dans l'ordre</template>
						<template v-else>carte</template>
						à associer avec cette carte noire.
					</p>
					<p v-else class="text-gray-700 mb-8">
						Les autres joueurs sélectionnent leurs réponses...
					</p>
				</template>
				<template v-else-if="round.state === 'completed' && !loading">
					<btn v-if="isHost" @click.native="newRound" class="mb-8" :disabled="loading">
						Lancer la manche suivante
					</btn>
					<p v-else class="text-gray-700 mb-8">En attente de l'hôte pour le lancement d'une nouvelle manche...</p>
				</template>
				<template v-else-if="round.state === 'reveal:cards'">
					<p v-if="isJuge()" class="text-gray-700 mb-8">Révélez les cartes que les joueurs ont lancé.</p>
					<p v-else class="text-gray-700 mb-8">Le juge est en train de révêler les cartes...</p>
				</template>
				<template v-else-if="round.state === 'select:winner'">
					<p v-if="isJuge()" class="text-gray-700 mb-8">Choisissez quel joueur gagne cette manche.</p>
					<p v-else class="text-gray-700 mb-8">Le juge est en train de choisir le gagnant...</p>
				</template>
			</hand>
		</template>

		<template v-if="room.state === 'terminated'">
			<div class="h-full w-full flex flex-col justify-center overflow-hidden pb-20">
				<div class="w-full max-w-xs mx-auto bg-white rounded-lg border border-gray-300 shadow-xl text-center p-8">
					<h1 class="text-lg text-blue-700">Partie terminée</h1>
					<hr class="my-6">
					<ul>
						<li v-for="(player, i) in players.sort((a, b) => b.pivot.score - a.pivot.score)" :key="i"
						    class="text-gray-700 my-2"
						>
							{{ player.username }} : {{ player.pivot.score }} points
						</li>
					</ul>
				</div>
			</div>
		</template>

	</main>
</template>

<script>
	import {mapGetters, mapState} from 'vuex'
	import Hand from './HandComponent';
	import Velocity from "velocity-animate"

	export default {
		components: {Hand},
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
		beforeMount() {
			window.winOrigin = {x: window.innerWidth / 2, y: window.innerHeight / 2};
		},
		async mounted() {
			await this.$store.dispatch('initialization', {room_id: this.room_id, public_channel: this.public_channel});

			echo.private(this.private_channel)
				.listen("NewRoundEvent", ({room, round, hand}) => {
					console.log("NewRoundEvent", {room, round, hand});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
					this.$store.commit('setHand', {cards: hand});
					this.blackCardAngle = Math.round((Math.random() * 30) - 15);
					this.$refs.hand.clearSelection();
					this.$forceUpdate();
				});

			this.loading = false;
		},
		data() {
			return {
				hoveredPlayer: null,
				blackCardAngle: Math.round((Math.random() * 30) - 15),
				loading: true,
			}
		},
		methods: {
			fakeCardBeforeEnter(el) {
				const angle = (Math.PI * 2) * Math.random();
				const radius = Math.sqrt(Math.pow(winOrigin.x, 2) + Math.pow(winOrigin.y, 2));
				el.style.top = (winOrigin.x + (Math.cos(angle) * radius)) + 'px';
				el.style.left = (winOrigin.y + (Math.sin(angle) * radius)) + 'px';
				el.style.transform = `translate(-50%,-50%) rotateZ(0deg)`;
			},
			fakeCardEnter(el, done) {
				const radius = 100 + (Math.random() * 100);
				const angle = (Math.PI * 2) * Math.random();
				Velocity(el, {
					left: (winOrigin.x + (Math.cos(angle) * radius)) + 'px',
					top: (winOrigin.y + (Math.sin(angle) * radius)) + 'px',
					rotateZ: (Math.random() * 360) + 'deg'
				}, {
					easing: "ease-out",
					delay: Math.floor(Math.random() * 250),
					duration: 500,
					complete: done
				});
			},
			fakeCardLeave(el, done) {
				const duration = 200 + Math.floor(Math.random() * 150);
				Velocity(el, {opacity: 0}, {duration, complete: done});
			},
			startRoom() {
				if (this.loading) return;
				this.loading = true;
				this.$store.dispatch('startRoom')
					.then((res) => {
						if (res !== true) {
							alert(res.message);
						}
						this.loading = false
					})
			},
			drawBlackCard() {
				this.$store.dispatch("drawBlackCard");
			},
			revealPlayer({id}) {
				if (this.loading) return;
				if (!this.$store.getters.isJuge()) return;
				if (this.$store.getters.isPlayerRevealed({id})) return;

				this.loading = true;

				this.$store.dispatch("revealPlayer", {id})
					.then((res) => {
						if (res !== true) {
							alert(res.message);
						}
						this.loading = false
					})
			},
			newRound() {
				if (this.loading) return;
				this.loading = true;
				this.$store.dispatch("newRound")
					.then(res => {
						if (res !== true) {
							alert(res.message);
						}
						this.loading = false
					})
			},
			getRevealedCard(card, player_id) {
				const c = {};

				if (this.$store.getters.isPlayerRevealed({id: player_id}))
					c.text = card.text;

				if (this.$store.state.round.state === "completed")
					c.contributor = {username: this.$store.getters.getPlayer(player_id).username}

				return c;
			},
			selectWinner({player_id}) {
				if (this.loading) return;
				if (!this.$store.getters.isJuge()) return;
				if (this.$store.state.round.state !== 'select:winner') return;

				this.loading = true;

				this.$store.dispatch("selectWinner", {player_id})
					.then((res) => {
						if (res !== true) {
							alert(res.message);
						}
						this.loading = false
					})
			}
		},
		computed: {
			...mapState({
				room: state => state.room,
				round: state => state.round,
				hand: state => state.hand,
				winner: state => state.winner || {}
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
				'getPlayer',
				'neededWhiteCards',
				'playedCardsCount'
			])
		}
	}
</script>

<style scoped>
	.roomTable {
		min-height: 20rem;
		/*height: 50vh;*/
	}


	.hand {
		transform: translateY(0em);
		transition: transform 250ms ease-out;
	}

</style>