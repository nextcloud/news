export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

export const APPLICATION_MUTATION_TYPES = {
	SET_ERROR: 'SET_ERROR',
}

type AppInfoState = {
	error?: any;
}

const state: AppInfoState = {
	error: undefined,
}

const getters = {
	error(state: AppInfoState) {
		return state.error
	},
}

export const actions = {
	// async [APPLICATION_ACTION_TYPES.SET_ERROR_MESSAGE]({ commit }: ActionParams) {

	// },
}

export const mutations = {
	[APPLICATION_MUTATION_TYPES.SET_ERROR](
		state: AppInfoState,
		error: any,
	) {
		state.error = error
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
