<template>
	<button class="font-bold text-center py-3 px-4 rounded block mx-auto focus:outline-none focus:shadow-outline"
	        :disabled="disabled"
	        :class="colorClasses">
		<slot></slot>
	</button>
</template>

<script>
	export default {
		name: "Button",
		props: {
			color: {
				type: String,
				required: false,
				default() { return 'blue'; }
			},
			disabled: {
				type: Boolean,
				required: false,
				default() { return false; }
			}
		},
		computed: {
			colorClasses() {
				const classes = [];
				switch (this.color) {
					case 'black':
						classes.push('bg-gray-900 text-gray-100 hover:bg-gray-700');
						break;
					default:
						classes.push('bg-blue-500 hover:bg-blue-700 text-white');
				}
				if (this.disabled) {
					classes.push('opacity-50');
				}
				return classes;
			}
		}
	}
</script>

<style scoped>
</style>