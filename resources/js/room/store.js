import Vuex from "vuex"
import Vue from "vue"

require('../bootstrap');
Vue.use(Vuex);

export default new Vuex.Store({
	strict: true,
	state: {
		user: null,
		room: {},
		round: {},
		hand: [],
		connectedPlayers: []
	},
	getters: {
		players: state => {
			if (!state.room.players)
				return [];

			return state.room.players.map(player => {
				player.connected = state.connectedPlayers.find((cp) => cp.id === player.id) !== undefined;
				return player
			})
		},
		isHost: state => {
			if (!state.room.host) return false;
			if (!state.user) return false;
			return state.room.host.id === state.user.id;
		},
		isJuge: state => player => {
			if (!state.room.juge) return false;
			if (player) {
				return state.room.juge.id === player.id;
			}
			return state.user && state.room.juge.id === state.user.id;
		},
		isRoomWaiting: state => state.room && state.room.state === "waiting",
		isRoomPlaying: state => state.room && state.room.state === "playing",
		isRoundDrawing: state => modifier => {
			if (!state.round) return false;
			if (modifier) {
				return state.round.state === `draw:${modifier}`;
			}
			return state.round.state.startsWith('draw');
		}
	},
	mutations: {
		setUser: (state, {user}) => {state.user = user},
		setRoom: (state, {room}) => {state.room = room},
		setRound: (state, {round}) => {state.round = round},
		setHand: (state, {cards}) => {state.hand = cards},
		addPlayers: (state, {players}) => {
			players.forEach(player => {
				if (!state.room.players.find(p => p.id === player.id))
					state.room.players.push(player);
			})
		},
		addConnectedPlayer: (state, {player}) => {
			if (!state.connectedPlayers.find(p => p.id === player.id))
				state.connectedPlayers.push(player);
		},
		removeConnectedPlayer: (state, {player}) => {
			if (state.connectedPlayers.find(p => p.id === player.id))
				state.connectedPlayers.splice(
					state.connectedPlayers.findIndex(p => p.id === player.id), 1);
		}
	},
	actions: {
		loadRoom: async function (ctx, {id}) {
			try {
				const response = await axios.get(`/api/room/${id}`);
				ctx.commit('setRoom', {room: response.data.room});
				ctx.commit('setRound', {round: response.data.round});
				ctx.commit('setHand', {cards: response.data.hand});
			} catch (error) {
				// TODO: handle error
				console.error(error);
			}
		},
		loadUser: async function (ctx) {
			try {
				const response = await axios.get('/api/user');
				ctx.commit('setUser', {user: response.data});
			} catch (error) {
				// TODO: handle error
				console.error(error);
			}
		},
		drawWhiteCard: async function (ctx, {amount}) {
			try {
				// TODO
				//const response = await axios.get('/api/room/' + id);
				//context.commit('setRoom', {room: response.data});
			} catch (e) {
				console.error(e)
			}
		},
		startRoom: async function (ctx) {
			if (!ctx.state.room.id) {
				console.error("The room has not been loaded.");
				return {"message": "The room has not been loaded."};
			}
			try {
				const response = await axios.post('/api/room/' + ctx.state.room.id + '/start');
				ctx.commit('setRoom', {room: response.data.room});
				ctx.commit('setRound', {round: response.data.round});
				ctx.commit('setHand', {cards: response.data.hand});
				return true;
			} catch (error) {
				console.error(error.response.data.message);
				return {"message": error.response.data.message};
			}
		}
	}
})