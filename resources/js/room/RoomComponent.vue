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
			<btn v-if="isHost && players.length >= 2" @click.native="startRoom" class="mt-4" :disabled="loading">
				Démarrer la partie
			</btn>
		</div>

		<div v-if="isRoomPlaying && isRoundDrawing()" class="h-full w-full flex items-center overflow-hidden pb-20">
			<div class="fixed top-0 left-0 w-0 h-0" style="z-index: 2">
				<card v-for="(fCard,j) in fakeCards" :card='{"text":""}' :key="j" size="xs" class="card--fake"
				      :style="{top: fCard.x + 'px', left: fCard.y + 'px',transform: 'translate(-50%,-50%) rotateZ('+ fCard.r +'deg)'}"/>
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

		<div v-if="isRoomPlaying && round.state.startsWith('reveal:')" class="h-full w-full flex flex-col items-center">
			<div class="flex justify-center items-center mx-16 mt-8 mb-10">
				<card :card="round.black_card" :style="{transform:'rotateZ('+ blackCardAngle +'deg)'}" size="xs"/>
			</div>
			<div class="flex-1 flex items-center justify-center">
				<div v-for="(cards, player_id) in round.played_cards" :key="parseInt(player_id)"
				     class="inline-block mb-6 mx-6">
					<div class="flex mb-4">
						<card v-for="(card,j) in cards" :key="j" :card="getRevealedCard(card, parseInt(player_id))"
						      :class="{'cursor-pointer': isJuge() && !isPlayerRevealed({id:parseInt(player_id)})}"
						      @click.native="revealPlayer({id:parseInt(player_id)})"
						      size="sm" :style="{transform:'rotateZ('+ ((j * 8)-4) +'deg)'}"/>
					</div>
				</div>
			</div>
		</div>

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
			<template v-else-if="round.state === 'reveal:usernames' && !loading">
				<btn v-if="isHost" @click.native="newRound" class="mb-8" :disabled="loading">
					Lancer la manche suivante
				</btn>
				<p v-else class="text-gray-700 mb-8">En attente de l'hôte pour le lancement d'une nouvelle manche...</p>
			</template>
			<template v-else-if="round.state === 'reveal:cards'">
				<p v-if="isJuge()" class="text-gray-700 mb-8">Révélez les cartes que les joueurs ont lancé.</p>
				<p v-else class="text-gray-700 mb-8">Le juge est en train de révêler les cartes...</p>
			</template>
		</hand>

	</main>
</template>

<script>
	import {mapGetters, mapState} from 'vuex'
	import Hand from './HandComponent';

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
		async mounted() {
			await this.$store.dispatch('initialization', {room_id: this.room_id, public_channel: this.public_channel})
				.then(channel => {channel.listen("CardsPlayedEvent", ({amount}) => {this.throwFakeCards(amount);})});

			echo.private(this.private_channel)
				.listen("NewRoundEvent", ({room, round, hand}) => {
					console.log("NewRoundEvent", {room, round, hand});
					this.$store.commit('setRoom', {room});
					this.$store.commit('setRound', {round});
					this.$store.commit('setHand', {cards: hand});
					this.blackCardAngle = Math.round((Math.random() * 30) - 15);
					this.$refs.hand.clearSelection();
					this.clearFakeCards();
					this.$forceUpdate();
				});

			if (this.$store.state.round.black_card) {
				this.throwFakeCards(this.$store.state.round.played_ids.length * this.neededWhiteCards);
			}
			this.loading = false;
		},
		data() {
			return {
				zoomOn: null,
				fakeCards: [],
				blackCardAngle: Math.round((Math.random() * 30) - 15),
				loading: true,
			}
		},
		methods: {
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
				if (!this.$store.getters.isPlayerRevealed({id: player_id}))
					return {};

				return {
					text: card.text,
					contributor: {
						username: this.$store.getters.getPlayer(player_id).username
					}
				}
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
				'isRoomPlaying',
				'isRoundDrawing',
				'hasPlayed',
				'isPlayerRevealed',
				'getPlayer',
				'neededWhiteCards'
			])
		}
	}
</script>

<style scoped>
	.roomTable {
		min-height: 20rem;
		/*height: 50vh;*/
	}


	.card--fake {
		position: fixed;
		z-index: 3;
	}


	.hand {
		transform: translateY(0em);
		transition: transform 250ms ease-out;
	}

</style>