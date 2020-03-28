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
		connectedPlayers: []
	},
	getters: {
		roomState: state => state.roomState,
		room: state => state.room,
		connectedPlayers: state => state.connectedPlayers,
		hand: state => state.hand,
		blackCard: state => state.blackCard
	},
	mutations: {
		setRoomState: (s, {state}) => {s.roomState = state},
		setRoom: (state, {room}) => {state.room = room},
		setBlackCard: (state, {card}) => {state.blackCard = card},
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
				context.commit('setBlackCard', {card: response.data.black_card});
				context.commit('setRoomState', {state: response.data.state});
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