import { loadState } from '@nextcloud/initial-state'
import { reactive } from 'vue'
import { DISPLAY_MODE, SPLIT_MODE } from '../enums/index.ts'
import { APPLICATION_MUTATION_TYPES } from '../types/MutationTypes.ts'

export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

export type AppInfoState = {
	error?: Error
	loading: boolean
	lastOpmlImportMessage: { type: string, message: string } | undefined
	lastArticlesImportMessage: { type: string, message: string } | undefined
	displaymode: string
	splitmode: string
	oldestFirst: boolean
	preventReadOnScroll: boolean
	showAll: boolean
	disableRefresh: boolean
	titleFilterRegex: string
	lastViewedFeedId: string
	lastViewedFeedType: string
	starredOpenState: boolean
}

const state: AppInfoState = reactive({
	error: undefined,
	loading: true,
	lastOpmlImportMessage: undefined,
	lastArticlesImportMessage: undefined,
	displaymode: loadState('news', 'displaymode', DISPLAY_MODE.DEFAULT),
	splitmode: loadState('news', 'splitmode', SPLIT_MODE.VERTICAL),
	oldestFirst: loadState('news', 'oldestFirst', null) === '1',
	preventReadOnScroll: loadState('news', 'preventReadOnScroll', null) === '1',
	showAll: loadState('news', 'showAll', null) === '1',
	disableRefresh: loadState('news', 'disableRefresh', null) === '1',
	titleFilterRegex: loadState('news', 'titleFilterRegex', ''),
	lastViewedFeedId: loadState('news', 'lastViewedFeedId', '0'),
	lastViewedFeedType: loadState('news', 'lastViewedFeedType', '6'),
	starredOpenState: loadState('news', 'starredOpenState', null) === '1',
})

const getters = {
	error(state: AppInfoState) {
		return state.error
	},
	loading(state: AppInfoState) {
		return state.loading
	},
	lastOpmlImportMessage(state: AppInfoState) {
		return state.lastOpmlImportMessage
	},
	lastArticlesImportMessage(state: AppInfoState) {
		return state.lastArticlesImportMessage
	},
	displaymode(state: AppInfoState) {
		return state.displaymode
	},
	splitmode(state: AppInfoState) {
		// ignore split mode when screenreader mode is set
		return state.displaymode === '2' ? '2' : state.splitmode
	},
	oldestFirst(state: AppInfoState) {
		return state.oldestFirst
	},
	preventReadOnScroll(state: AppInfoState) {
		return state.preventReadOnScroll
	},
	showAll(state: AppInfoState) {
		return state.showAll
	},
	disableRefresh(state: AppInfoState) {
		return state.disableRefresh
	},
	titleFilterRegex(state: AppInfoState) {
    	return state.titleFilterRegex
	},
	lastViewedFeedId(state: AppInfoState) {
		return state.lastViewedFeedId
	},
	lastViewedFeedType(state: AppInfoState) {
		return state.lastViewedFeedType
	},
	starredOpenState(state: AppInfoState) {
		return state.starredOpenState
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
	[APPLICATION_MUTATION_TYPES.SET_OPML_IMPORT_MESSAGE](
		state: AppInfoState,
		{ value }: { value: { type: string, message: string } },
	) {
		state.lastOpmlImportMessage = value
	},
	[APPLICATION_MUTATION_TYPES.SET_ARTICLES_IMPORT_MESSAGE](
		state: AppInfoState,
		{ value }: { value: { type: string, message: string } },
	) {
		state.lastArticlesImportMessage = value
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
	titleFilterRegex(
    state: AppInfoState,
    { value }: { value: string },
	) {
    	state.titleFilterRegex = value
	},
	starredOpenState(
		state: AppInfoState,
		{ value }: { value: boolean },
	) {
		state.starredOpenState = value
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
