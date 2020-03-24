<template>
	<div v-bind:class="{ 'bg-white text-gray-700': !isQuestion, 'bg-gray-900 text-gray-100': isQuestion }"
	     class="w-56 h-64 m-4 border border-gray-300 rounded-lg p-5 flex flex-col justify-between shadow-lg text-lg">
		<!--		<p v-if="editable" class="border" contenteditable="true" @input="updateText"></p>-->
		<p :class="{'text-gray-500':placeholder && !card.text}">{{ placeholder && !card.text ? placeholder : card.text }}</p>
		<cite v-if="card.contributor" class="text-gray-500 d-block mt-2 text-xs text-right italic">
			par {{ card.contributor.username }}
		</cite>
	</div>
</template>

<script>
	export default {
		name: "CardComponent",
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
			editable: false
		},
		created() {
			console.log(this.card)
		},
		computed: {
			isQuestion: function () {
				return new RegExp(/_{2,}/gm).test(this.card.text)
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

</style>