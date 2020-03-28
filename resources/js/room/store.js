import Vuex from "vuex"
import Vue from "vue"

require('../bootstrap');
Vue.use(Vuex);

export default new Vuex.Store({
	strict: true,
	state: {
		room: {},
		round: {},
		hand: [],
		connectedPlayers: []
	},
	getters: {
		room: state => state.room,
		round: state => state.round,
		hand: state => state.hand,
		connectedPlayers: state => state.connectedPlayers,
		players: state => {
			return state.room.players.map(player => {
				player.connected = state.connectedPlayers.find((cp) => cp.id === player.id) !== undefined;
				return player
			})
		}
	},
	mutations: {
		setRound: (state, {round}) => {state.round = round},
		setRoom: (state, {room}) => {state.room = room},
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
		loadRoom: async function (context, {id}) {
			try {
				const response = await axios.get('/api/room/' + id);
				context.commit('setRoom', {room: response.data.room});
				context.commit('setRound', {state: response.data.round});
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
		},
		startRoom: async function (ctx) {
			if (!ctx.state.room.id) {
				console.error("The room has not been loaded.");
				return {"message": "The room has not been loaded."};
			}
			try {
				const response = await axios.post('/api/room/' + ctx.state.room.id + '/start');
				ctx.commit('setRoomState', {state: response.data.state});
				return true;
			} catch (error) {
				console.error(error.response.data.message);
				return {"message": error.response.data.message};
			}
		}
	}
})