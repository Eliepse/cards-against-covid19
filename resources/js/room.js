import Vue from "vue"
import store from './room/store';
import Axios from 'axios'
import Room from './room/RoomComponent'

Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios = Axios;
window.io = require('socket.io-client');

new Vue({
	el: '#room',
	components: {Room},
	store
});