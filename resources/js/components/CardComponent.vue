<template>
	<div class="card inline-block m-4 border border-gray-300 rounded-lg flex flex-col justify-between shadow-lg text-lg text-left"
	     :class="[...backgroundClasses, ...sizeClasses]">
		<p :class="{'text-gray-500':placeholder && !card.text}">
			{{ placeholder && !card.text ? placeholder : card.text }}
		</p>
		<cite v-if="card.contributor" class="text-gray-500 d-block mt-2 text-xs text-right italic">
			par {{ card.contributor.username }}
		</cite>
	</div>
</template>

<script>
	export default {
		props: {
			card: {
				type: Object,
				required: true,
				default: function () {
					return {
						text: null,
						contributor: null
					}
				}
			},
			placeholder: {
				type: String,
				required: false,
				default: null
			},
			size: {
				type: String,
				required: false,
				default() { return 'medium'; }
			},
			editable: false,
		},
		data() {
			return {
				hovered: false
			}
		},
		computed: {
			isBlack: function () {
				return new RegExp(/_{2,}/gm).test(this.card.text)
			},
			backgroundClasses() {
				let classes = [];
				if (this.isBlack) {
					classes.push('bg-gray-900');
					classes.push('text-gray-100');
				} else {
					classes.push('bg-white');
					classes.push('text-gray-700');
				}
				return classes;
			},
			sizeClasses() {
				switch (this.size) {
					case 'xs':
						return ['card--xsmall'];
					case 'sm':
						return ['card--small'];
					default:
						return [];
				}
			}
		},
		methods: {
			//updateText: function (e) {
			//	this.text = e.target.innerHTML;
			//}
		}
	}
</script>

<style scoped>
	.card {
		position: relative;
		padding: 1.25rem;
		width: 11em;
		height: 12.5em;
		user-select: none;
		transition: transform 35ms;
	}


	/*noinspection CssUnusedSymbol*/
	.card--xsmall {
		margin: 0 -.825em;
		padding: .825rem;
		font-size: .825rem;
		width: 9em;
		height: 10.5em;
	}


	/*noinspection CssUnusedSymbol*/
	.card--small {
		margin: 0 -.825em;
		padding: 1rem;
		font-size: .825rem;
		width: 10em;
		height: 11.5em;
	}
</style>