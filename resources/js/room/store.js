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
		},
		hasPlayed: state => player => {
			if (!state.round.played_ids) return false;
			return state.round.played_ids.includes(player ? player.id : state.user.id);
		},
		isPlayerRevealed: state => ({id}) => state.round.revealed_ids.includes(id),
		getPlayer: state => id => state.room.players.find(({id: pid}) => pid === id),
		neededWhiteCards: function (state) {
			if (!state.round.black_card) return 0;
			return state.round.black_card.blanks;
		}
	},
	mutations: {
		setUser: (state, {user}) => {state.user = user},
		setRoom: (state, {room}) => {state.room = room},
		setRound: (state, {round}) => {state.round = round},
		setHand: (state, {cards}) => {state.hand = cards},
		setBlackCard: (state, {card}) => {state.round.black_card = card},
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
		initialization: async function (ctx, {room_id, public_channel}) {
			await ctx.dispatch('loadUser');
			await ctx.dispatch('loadRoom', {id: room_id});

			return echo.join(public_channel)
				.here(players => players.forEach(player => ctx.commit('addConnectedPlayer', {player})))
				.joining(player => ctx.commit('addConnectedPlayer', {player}))
				.leaving(player => ctx.commit('removeConnectedPlayer', {player}))
				.listen("PlayerJoinedEvent", ({players}) => {
					console.log("PlayerJoinedEvent", {players});
					ctx.commit('addPlayers', {players})
				})
				.listen("PlayerLeftEvent", ({room, player}) => {
					console.log("PlayerJoinedEvent", {room, player});
					ctx.commit('setRoom', {room})
				})
				.listen("CardsPlayedEvent", ({room, round, amount}) => {
					console.log("CardsPlayedEvent", {room, round, amount});
					ctx.commit('setRoom', {room});
					ctx.commit('setRound', {round});
				})
				.listen("StateChangedEvent", ({room, round}) => {
					console.log("StateChangedEvent", {room, round});
					ctx.commit('setRoom', {room});
					ctx.commit('setRound', {round});
				})
				.listen("PlayerRevealedEvent", ({room, round, player}) => {
					console.log("PlayerRevealedEvent", {room, round, player});
					ctx.commit('setRoom', {room});
					ctx.commit('setRound', {round});
				});
		},
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
		drawBlackCard: async function (ctx) {
			try {
				const response = await axios.post(`/api/room/${this.state.room.id}/draw:black-card`);
				ctx.commit('setRound', {round: response.data.round});
				ctx.commit('setHand', {cards: response.data.hand});
				return true;
			} catch (error) {
				console.error(error);
				return {"message": error.response.data.message};
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
		},
		playCards: async function (ctx, {cards}) {
			try {
				const response = await axios.post(
					`/api/room/${this.state.room.id}/play:white-cards`,
					{cards: cards.map(card => card.id)}
				);
				ctx.commit('setRoom', {room: response.data.room});
				ctx.commit('setRound', {round: response.data.round});
				ctx.commit('setHand', {cards: response.data.hand});
				return true;
			} catch (error) {
				console.error(error.response.data.message);
				return {"message": error.response.data.message};
			}
		},
		revealPlayer: async function (ctx, {id}) {
			try {
				const response = await axios.post(
					`/api/room/${this.state.room.id}/reveal:player`,
					{player_id: id}
				);
				ctx.commit('setRoom', {room: response.data.room});
				ctx.commit('setRound', {round: response.data.round});
				return true;
			} catch (error) {
				console.error(error.response.data.message);
				return {"message": error.response.data.message};
			}
		},
		newRound: async function (ctx) {
			try {
				const response = await axios.post(`/api/room/${this.state.room.id}/round`);
				ctx.commit('setRoom', {room: response.data.room});
				ctx.commit('setRound', {round: response.data.round});
				return true;
			} catch (error) {
				console.error(error.response.data.message);
				return {"message": error.response.data.message};
			}
		}
	}
})