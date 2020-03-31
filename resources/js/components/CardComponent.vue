<template>
	<div :class="classes"
	     class="card inline-block m-4 border border-gray-300 rounded-lg flex flex-col justify-between shadow-lg p-5 text-lg text-left">
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
			editable: false,
			small: false
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
			classes() {
				let classes = [];
				classes.push(this.isBlack ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-700');
				if (this.small) classes.push('card--small');
				return classes;
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
		width: 11em;
		height: 12.5em;
		user-select: none;
		transition: transform 35ms;
	}


	/*noinspection CssUnusedSymbol*/
	.card--small {
		position: relative;
		margin: 0 -1em;
		font-size: .825rem;
		width: 11em;
		height: 12.5em;
	}
</style>