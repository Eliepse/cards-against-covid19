import Vue from "vue"
import store from './room/store';

require('./bootstrap');

new Vue({
	el: '#room',
	store
});