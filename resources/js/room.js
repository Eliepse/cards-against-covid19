import Vue from "vue"
import store from './room/store';
import Axios from 'axios'
import Room from './room/RoomComponent'
import CardComponent from './components/CardComponent';
import ButtonComponent from './components/ButtonComponent';
import Echo from 'laravel-echo';

Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios = Axios;
window.io = require('socket.io-client');
window.echo = new Echo({
	broadcaster: 'socket.io',
	host: window.location.hostname,
	namespace: 'App.Events.Room'
});

Vue.component("Card", CardComponent);
Vue.component("Btn", ButtonComponent);

new Vue({
	el: '#room',
	components: {Room},
	store
});