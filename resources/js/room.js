import Vue from "vue"
import store from './room/store';
import Axios from 'axios'
import Echo from 'laravel-echo'

Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios = Axios;
window.io = require('socket.io-client');

new Vue({
	el: '#room',
	store
});