<template>
	<div class="hand w-full text-center"
	     :class="{'hand--hide':hidden}">
		<div class="hand-header">
			<slot v-if="!selectionComplete"></slot>
			<button v-else
			        @click="playSelection"
			        :disabled="submitting"
			        class="mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold text-center
									py-3 px-4 rounded focus:outline-none focus:shadow-outline block mb-8">
				<template v-if="selection.length > 1">Jouer ces cartes</template>
				<template v-else>Jouer cette carte</template>
			</button>
		</div>
		<div class="hand-cards flex justify-center">
			<Card v-for="(card, i) in cards" :key="i" :card="shown ? card : {}" size="xs"
			      :class="{'card--selected': isCardSelected(card), 'cursor-pointer':shown}"
			      :style="cardStyles(card, i)"
			      @mouseenter.native="zoomedCard = card"
			      @mouseleave.native="zoomedCard = null"
			      @click.native="toggleCardSelection(card)"/>
		</div>
	</div>
</template>

<script>
	export default {
		name: "HandComponent",
		props: {
			cards: {
				type: Array,
				required: false,
				default() {return [];}
			},
			needed: {
				type: Number,
				required: false,
				default() {return 0;}
			}
		},
		created() {
			for (let i = 0; i < 10; i++) {
				this.rotations.push(Math.round(Math.random() * 12) - 6)
			}
		},
		data() {
			return {
				zoomedCard: null,
				rotations: [],
				selection: [],
				visible: true,
				submitting: false,
			}
		},
		methods: {
			show() { this.visible = true; },
			hide() { this.visible = false; },
			cardStyles(card, i) {
				const rotation = `rotateZ(${this.rotations[i]}deg)`;

				if (this.shown && this.zoomedCard === card) {
					return {
						zIndex: this.cards.length + 5,
						transform: `${rotation} scale(1.3) translateY(-15%)`
					}
				}

				return {
					zIndex: i + 5,
					transform: `${rotation} scale(1) translateY(0%)`
				};
			},
			toggleCardSelection(card) {
				if (this.hidden) return;
				const i = this.selection.findIndex((c) => c.id === card.id);
				if (i >= 0) {
					this.selection.splice(i, 1);
					return;
				}
				if (this.selection.length < this.needed) {
					this.selection.push(card);
					this.$emit('selection', {
						hand: this,
						cards: this.selection,
						complete: this.selectionComplete
					});
				}
			},
			clearSelection() {
				this.selection = [];
			},
			playSelection() {
				if (!this.selectionComplete) {
					this.submitting = false;
					return;
				}
				if (this.submitting) return;
				this.hide();
				this.$store.dispatch("playCards", {cards: this.selection})
					.then(res => {
						if (res !== true) {
							this.submitting = false;
							alert(res.message);
						}
						this.submitting = false;
						this.clearSelection();
						this.show();
					});
			},
			isCardSelected(card) {
				return this.selection.find((c) => c.id === card.id);
			},
		},
		computed: {
			shown() {
				return this.visible
					&& this.needed > 0
					&& !this.$store.getters.isJuge()
					&& this.$store.getters.isRoundDrawing('white-card')
					&& !this.$store.getters.hasPlayed();
			},
			hidden() {
				return !this.shown;
			},
			selectionComplete() {
				return this.needed > 0 && this.selection.length === this.needed
			}
		}
	}
</script>

<style scoped>
	.hand-cards {
		margin-bottom: -.5em;
		transition: transform 300ms ease-out;
	}


	.hand--hide .hand-cards {
		margin-bottom: -6em;
	}


	/*noinspection CssUnusedSymbol*/
	.card--selected {
		border-color: #4299e1;
		transform: scale(1) translateY(-10%);
	}
</style>