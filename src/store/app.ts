import { loadState } from '@nextcloud/initial-state'
import { APPLICATION_MUTATION_TYPES } from '../types/MutationTypes'

export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

export type AppInfoState = {
	error?: Error;
	loading: boolean;
	displaymode: string;
	splitmode: string;
	oldestFirst: boolean;
	preventReadOnScroll: boolean;
	showAll: boolean;
	disableRefresh: boolean;
	lastViewedFeedId: string;
	lastViewedFeedType: string;
}

const state: AppInfoState = {
	error: undefined,
	loading: true,
	displaymode: loadState('news', 'displaymode', '0'),
	splitmode: loadState('news', 'splitmode', '0'),
	oldestFirst: loadState('news', 'oldestFirst', null) === '1',
	preventReadOnScroll: loadState('news', 'preventReadOnScroll', null) === '1',
	showAll: loadState('news', 'showAll', null) === '1',
	disableRefresh: loadState('news', 'disableRefresh', null) === '1',
	lastViewedFeedId: loadState('news', 'lastViewedFeedId', '0'),
	lastViewedFeedType: loadState('news', 'lastViewedFeedType', '6'),
}

const getters = {
	error(state: AppInfoState) {
		return state.error
	},
	loading() {
		return state.loading
	},
	displaymode() {
		return state.displaymode
	},
	splitmode() {
		// ignore split mode when screenreader mode is set
		return state.displaymode === '2' ? '2' : state.splitmode
	},
	oldestFirst() {
		return state.oldestFirst
	},
	preventReadOnScroll() {
		return state.preventReadOnScroll
	},
	showAll() {
		return state.showAll
	},
	disableRefresh() {
		return state.disableRefresh
	},
	lastViewedFeedId() {
		return state.lastViewedFeedId
	},
	lastViewedFeedType() {
		return state.lastViewedFeedType
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
	[APPLICATION_MUTATION_TYPES.SET_LOADING](
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.loading = value
	},
	displaymode(
		state: AppInfoState,
		{ value }: { value: string },
	) {
		state.displaymode = value
	},
	splitmode(
		state: AppInfoState,
		{ value }: { value: string },
	) {
		state.splitmode = value
	},
	oldestFirst(
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.oldestFirst = value
	},
	preventReadOnScroll(
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.preventReadOnScroll = value
	},
	showAll(
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.showAll = value
	},
	disableRefresh(
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.disableRefresh = value
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
