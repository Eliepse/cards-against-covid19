<template>
	<main class="container mx-auto py-10">

		<h1 class="text-3xl text-blue-700 text-center">Outil de création de carte</h1>
		<p class="text-gray-700 text-center text-sm mt-2 mb-4">
			Évitez les doubons en consultant la
			<a class="text-blue-700 hover:text-blue-900 underline" href="/cards">liste des cartes.</a>
		</p>

		<div class="max-w-xl m-auto p-6">
			<div class="flex justify-between items-center">
				<Card :card="editCard" :placeholder="placeholder"></Card>
				<form @submit.prevent="send" class="my-6 text-center">
					<label class="text-lg"
					       for="text">Texte de la carte</label><br>
					<textarea id="text"
					          name="text"
					          v-model="editCard.text"
					          :disabled="sending"
					          class="resize-none px-3 py-2 my-4 placeholder-gray-600 h-24 rounded-md border border-gray-300"
					          :placeholder="placeholder"></textarea><br>
					<button type="submit"
					        :disabled="sending"
					        :class="{'opacity-25': sending}"
					        class="w-full bg-gray-300 hover:bg-gray-400 focus:bg-gray-400 px-4 py-2 rounded-md text-lg">
						Ajouter
					</button>
				</form>
			</div>
			<p :class="{'hidden': !errorMsg}" class="text-red-700 text-center text-sm mt-2">{{ errorMsg }}</p>
		</div>

		<hr class="border-gray-400 max-w-sm m-auto my-8">
		<h2 class="max-w-sm m-auto mb-2 text-center text-xl text-blue-700">Mes derniers ajouts</h2>

		<div class="flex flex-row flex-wrap justify-center">
			<Card v-for="(card, index) in lastCreated" :key="index" :card="card"></Card>
		</div>

	</main>
</template>

<script>
	import Card from './CardComponent'

	export default {
		components: {Card},
		created() {
			axios.get('/cards', {
				params: {
					"user_id": this.userid,
					limit: 50
				}
			})
				.then((response) => {
					this.lastCreated = response.data.cards;
				})
		},
		props: {
			userid: {
				type: Number,
				required: true
			},
			lastcreated: {
				type: Array,
				default: function () {return []; }
			}
		},
		data() {
			return {
				editCard: {
					text: null
				},
				sending: false,
				errorMsg: "",
				placeholder: 'Ajouter des ____ pour rédiger une carte à trous.',
				lastCreated: this.lastcreated
			}
		},
		methods: {
			send: function () {
				if (this.sending || this.editCard.text.trim().length < 6) return;
				this.sending = true;
				this.errorMsg = "";
				axios.post("/cards", {"text": this.editCard.text.trim()})
					.then((response) => {
						this.lastCreated.unshift(response.data.card);
						this.editCard.text = "";
						this.sending = false;
					})
					.catch((response) => {
						console.log(response);
						this.sending = false;
						this.errorMsg = response.message;
					})
			}
		}
	}
</script>

<style scoped>

</style>