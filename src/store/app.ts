import { loadState } from '@nextcloud/initial-state'
import { reactive } from 'vue'
import { DISPLAY_MODE, MEDIA_TYPE, SHOW_MEDIA, SPLIT_MODE } from '../enums/index.ts'
import { APPLICATION_MUTATION_TYPES } from '../types/MutationTypes.ts'

export const APPLICATION_ACTION_TYPES = {
	SET_ERROR_MESSAGE: 'SET_ERROR_MESSAGE',
}

interface MediaOptions {
	[MEDIA_TYPE.THUMBNAILS]: number
	[MEDIA_TYPE.IMAGES]: number
	[MEDIA_TYPE.IMAGES_BODY]: number
	[MEDIA_TYPE.IFRAMES_BODY]: number
}

export type AppInfoState = {
	error?: Error
	loading: boolean
	lastOpmlImportMessage: { type: string, message: string } | undefined
	lastArticlesImportMessage: { type: string, message: string } | undefined
	mediaOptions: MediaOptions
	displaymode: string
	splitmode: string
	oldestFirst: boolean
	preventReadOnScroll: boolean
	showAll: boolean
	disableRefresh: boolean
	lastViewedFeedId: string
	lastViewedFeedType: string
	starredOpenState: boolean
}

export const defaultMediaOptions: MediaOptions = {
	[MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.ALWAYS,
	[MEDIA_TYPE.IMAGES]: SHOW_MEDIA.ALWAYS,
	[MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS,
	[MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ALWAYS,
}

/**
 *
 * @param value mediaOptions as string
 */
export function parseMediaOptions(value: string) {
	try {
		const parsed = JSON.parse(value)
		if (typeof parsed === 'object' && parsed !== null && !Array.isArray(parsed)) {
			return {
				[MEDIA_TYPE.THUMBNAILS]: parsed[MEDIA_TYPE.THUMBNAILS] ?? SHOW_MEDIA.ALWAYS,
				[MEDIA_TYPE.IMAGES]: parsed[MEDIA_TYPE.IMAGES] ?? SHOW_MEDIA.ALWAYS,
				[MEDIA_TYPE.IMAGES_BODY]: parsed[MEDIA_TYPE.IMAGES_BODY] ?? SHOW_MEDIA.ALWAYS,
				[MEDIA_TYPE.IFRAMES_BODY]: parsed[MEDIA_TYPE.IFRAMES_BODY] ?? SHOW_MEDIA.ALWAYS,
			}
		}
		return defaultMediaOptions
	} catch (error) {
		console.error('Failed to parse json settings string:', error)
		return defaultMediaOptions
	}
}

const state: AppInfoState = reactive({
	error: undefined,
	loading: true,
	mediaOptions: parseMediaOptions(loadState('news', 'mediaOptions', '{}')),
	lastOpmlImportMessage: undefined,
	lastArticlesImportMessage: undefined,
	displaymode: loadState('news', 'displaymode', DISPLAY_MODE.DEFAULT),
	splitmode: loadState('news', 'splitmode', SPLIT_MODE.VERTICAL),
	oldestFirst: loadState('news', 'oldestFirst', null) === '1',
	preventReadOnScroll: loadState('news', 'preventReadOnScroll', null) === '1',
	showAll: loadState('news', 'showAll', null) === '1',
	disableRefresh: loadState('news', 'disableRefresh', null) === '1',
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
	mediaOptions(state: AppInfoState) {
		return state.mediaOptions
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
	mediaOptions(
		state: AppInfoState,
		{ value }: { value: string },
	) {
		try {
			state.mediaOptions = JSON.parse(value)
		} catch (error) {
			console.error('Failed to set media settings using defaults:', error)
			state.mediaOptions = defaultMediaOptions
		}
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
