import Vuex from "vuex"
import Vue from "vue"

require('../bootstrap');
Vue.use(Vuex);

export default new Vuex.Store({
	strict: true,
	state: {
		hand: [],
		blackCard: null,
		room: {},
		roomState: null,
		players: [
			{"username": "Adr1"},
			{"username": "Agathe"},
			{"username": "Evou"},
			{"username": "Pangolin"},
		]
	},
	getters: {
		roomState: state => state.roomState,
		room: state => state.room,
		players: state => state.players,
		hand: state => state.hand,
		blackCard: state => state.blackCard
	},
	mutations: {
		setRoomState: (s, {state}) => {s.roomState = state},
		setRoom: (state, {room}) => {state.room = room},
		setBlackCard: (state, {card}) => {state.blackCard = card},
		addPlayer: (state, {player}) => {
			if (state.players.find(p => p.id === player.id)) {
				return;
			}

			state.players.push(player);
		},
		setPlayers: (state, {players}) => {
			state.players = players;
		}
	},
	actions: {
		loadRoom: async function (context, {id}) {
			try {
				const response = await axios.get('/api/room/' + id);
				context.commit('setRoom', {room: response.data.room});
				context.commit('setBlackCard', {card: response.data.black_card});
				context.commit('setPlayers', {players: response.data.connected_players});
				context.commit('setRoomState', {state: response.data.state});
				// load room state
			} catch (error) {
				console.error(error);
			}
		},
		drawWhiteCard: async function (context, {amount}) {
			try {
				// TODO
				//const response = await axios.get('/api/room/' + id);
				//context.commit('setRoom', {room: response.data});
			} catch (e) {
				console.error(e)
			}
		}
	}
})