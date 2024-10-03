import { loadState } from '@nextcloud/initial-state'
import { APPLICATION_MUTATION_TYPES } from '../types/MutationTypes'

export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

export type AppInfoState = {
	error?: Error;
}

const state: AppInfoState = {
	error: undefined,
	compact: loadState('news', 'compact') === '1',
	compactExpand: loadState('news', 'compactExpand') === '1',
	oldestFirst: loadState('news', 'oldestFirst') === '1',
	preventReadOnScroll: loadState('news', 'preventReadOnScroll') === '1',
	showAll: loadState('news', 'showAll') === '1'
}

const getters = {
	error(state: AppInfoState) {
		return state.error
	},
	compact() {
		return state.compact
	},
	compactExpand() {
		return state.compactExpand
	},
	oldestFirst() {
		return state.oldestFirst
	},
	preventReadOnScroll() {
		return state.preventReadOnScroll
	},
	showAll() {
		return state.showAll
	}
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
	compact (
		state: AppInfoState,
		{ value }: { value: newValue },
	) {
		state.compact = value
	},
	compactExpand (
		state: AppInfoState,
		{ value }: { value: newValue },
	) {
		state.compactExpand = value
	},
	oldestFirst (
		state: AppInfoState,
		{ value }: { value: newValue },
	) {
		state.oldestFirst = value
	},
	preventReadOnScroll (
		state: AppInfoState,
		{ value }: { value: newValue },
	) {
		state.preventReadOnScroll = value
	},
	showAll(
		state: AppInfoState,
		{ value }: { value: newValue },
	) {
		state.showAll = value
	}
}

export default {
	state,
	getters,
	actions,
	mutations,
}
