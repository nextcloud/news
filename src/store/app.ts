import { APPLICATION_MUTATION_TYPES } from '../types/MutationTypes'

export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

type AppInfoState = {
	error?: Error;
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
	// async [APPLICATION_ACTION_TYPES...]({ commit }: ActionParams) {

	// },
}

export const mutations = {
	[APPLICATION_MUTATION_TYPES.SET_ERROR](
		state: AppInfoState,
		error: Error,
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
