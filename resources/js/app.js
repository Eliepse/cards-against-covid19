import Vue from "vue"
import Card from './components/CardComponent'
import CardForm from './components/CardFormComponent'

require('./bootstrap');

new Vue({
	el: '#app',
	components: {Card, CardForm}
});
