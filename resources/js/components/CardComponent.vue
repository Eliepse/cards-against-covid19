<template>
	<div v-bind:class="{ 'bg-white text-gray-700': !isQuestion, 'bg-gray-900 text-gray-100': isQuestion }"
	     class="w-56 h-64 m-4 border border-gray-300 rounded-lg p-5 flex flex-col justify-between shadow-lg text-lg">
		<p v-if="editable" class="border" contenteditable="true" @input="updateText"></p>
		<p v-if="!editable" :class="{'text-gray-500':placeholder && !text}">{{ placeholder && !text ? placeholder : text }}</p>
		<cite v-if="contributor" class="text-gray-500 d-block mt-2 text-xs text-right italic">
			par {{ contributor }}
		</cite>
	</div>
</template>

<script>
	export default {
		name: "CardComponent",
		props: {
			text: {
				type: String,
				default: ""
			},
			placeholder: {
				type: String,
				default: null
			},
			contributor: null,
			editable: false
		},
		computed: {
			isQuestion: function () {
				return new RegExp(/_{2,}/gm).test(this.text)
			}
		},
		methods: {
			updateText: function (e) {
				this.text = e.target.innerHTML;
			}
		}
	}
</script>

<style scoped>

</style>