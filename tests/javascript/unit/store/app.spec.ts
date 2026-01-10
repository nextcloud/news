import type { AppInfoState } from '../../../../src/store/app.ts'

import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createStore } from 'vuex'
import { DISPLAY_MODE, SPLIT_MODE } from '../../../../src/enums/index.ts'
import appInfo from '../../../../src/store/app.ts'
import { mutations } from '../../../../src/store/app.ts'
import { APPLICATION_MUTATION_TYPES } from '../../../../src/types/MutationTypes.ts'

vi.mock('@nextcloud/router')

describe('app.ts', () => {
	'use strict'

	describe('getters', () => {
		let store: ReturnType<typeof createStore>

		beforeEach(() => {
			store = createStore({
				modules: {
					appInfo,
				},
			})
		})

		it('should return error message', () => {
			const error = new Error('test')
			store.state.appInfo.error = error
			expect(store.getters.error).toEqual(error)
		})

		it('should return loading state', () => {
			store.state.appInfo.loading = true
			expect(store.getters.loading).toEqual(true)
		})

		it('should return lastOpmlImportMessage from state', () => {
			const value = { type: 'error', message: 'Error uploading file' }
			store.state.appInfo.lastOpmlImportMessage = value
			expect(store.getters.lastOpmlImportMessage).toEqual(value)
		})

		it('should return lastArticlesImportMessage from state', () => {
			const value = { type: 'error', message: 'Error uploading file' }
			store.state.appInfo.lastArticlesImportMessage = value
			expect(store.getters.lastArticlesImportMessage).toEqual(value)
		})

		it('should return display mode', () => {
			store.state.appInfo.displaymode = DISPLAY_MODE.COMPACT
			expect(store.getters.displaymode).toBe(DISPLAY_MODE.COMPACT)
		})

		it('should return split mode', () => {
			store.state.appInfo.displaymode = DISPLAY_MODE.DEFAULT
			store.state.appInfo.splitmode = SPLIT_MODE.VERTICAL
			expect(store.getters.splitmode).toBe(SPLIT_MODE.VERTICAL)
		})

		it('should not return split mode when screenreader display mode is set', () => {
			store.state.appInfo.displaymode = DISPLAY_MODE.SCREENREADER
			store.state.appInfo.splitmode = SPLIT_MODE.VERTICAL
			expect(store.getters.splitmode).toBe(SPLIT_MODE.OFF)
		})

		it('should return oldestFirst state', () => {
			store.state.appInfo.oldestFirst = true
			expect(store.getters.oldestFirst).toBe(true)
		})

		it('should return preventReadOnScroll state', () => {
			store.state.appInfo.preventReadOnScroll = true
			expect(store.getters.preventReadOnScroll).toBe(true)
		})

		it('should return showAll state', () => {
			store.state.appInfo.showAll = true
			expect(store.getters.showAll).toBe(true)
		})

		it('should return disableRefresh state', () => {
			store.state.appInfo.disableRefresh = true
			expect(store.getters.disableRefresh).toBe(true)
		})

		it('should return lastViewedFeedId state', () => {
			store.state.appInfo.lastViewedFeedId = '1'
			expect(store.getters.lastViewedFeedId).toBe('1')
		})

		it('should return lastViewedFeedType state', () => {
			store.state.appInfo.lastViewedFeedType = '6'
			expect(store.getters.lastViewedFeedType).toBe('6')
		})

		it('should return starredOpenState state', () => {
			store.state.appInfo.starredOpenState = true
			expect(store.getters.starredOpenState).toBe(true)
		})
	})

	// describe('actions', () => {

	// })

	describe('mutations', () => {
		it('SET_ERROR should update the error in the state', () => {
			const state = { error: undefined } as AppInfoState

			const error = { message: 'test err' }

			mutations[APPLICATION_MUTATION_TYPES.SET_ERROR](state, error)
			expect(state.error).toEqual(error)

			mutations[APPLICATION_MUTATION_TYPES.SET_ERROR](state, undefined)
			expect(state.error).toEqual(undefined)
		})

		it('SET_LOADING should update loading flag in the state', () => {
			const state = { loading: undefined } as AppInfoState

			mutations[APPLICATION_MUTATION_TYPES.SET_LOADING](state, { value: true })
			expect(state.loading).toEqual(true)

			mutations[APPLICATION_MUTATION_TYPES.SET_LOADING](state, { value: false })
			expect(state.loading).toEqual(false)
		})

		it('SET_OPML_IMPORT_MESSAGE should update the value in the state', () => {
			const state = { value: { type: undefined, message: undefined } } as AppInfoState
			const value = { type: 'error', message: 'Error uploading file' }

			mutations[APPLICATION_MUTATION_TYPES.SET_OPML_IMPORT_MESSAGE](state, { value })
			expect(state.lastOpmlImportMessage).toEqual(value)
		})

		it('SET_ARTICLES_IMPORT_MESSAGE should update the value in the state', () => {
			const state = { value: { type: undefined, message: undefined } } as AppInfoState
			const value = { type: 'error', message: 'Error uploading file' }

			mutations[APPLICATION_MUTATION_TYPES.SET_ARTICLES_IMPORT_MESSAGE](state, { value })
			expect(state.lastArticlesImportMessage).toEqual(value)
		})

		it('displaymode should update the value in the state', () => {
			const state = { displaymode: undefined } as AppInfoState

			mutations.displaymode(state, { value: DISPLAY_MODE.DEFAULT })
			expect(state.displaymode).toEqual(DISPLAY_MODE.DEFAULT)
		})

		it('splitmode should update the value in the state', () => {
			const state = { splitmode: undefined } as AppInfoState

			mutations.splitmode(state, { value: SPLIT_MODE.OFF })
			expect(state.splitmode).toEqual(SPLIT_MODE.OFF)
		})

		it('oldestFirst should update the value in the state', () => {
			const state = { oldestFirst: undefined } as AppInfoState

			mutations.oldestFirst(state, { value: true })
			expect(state.oldestFirst).toEqual(true)
		})

		it('preventReadOnScroll should update the value in the state', () => {
			const state = { preventReadOnScroll: undefined } as AppInfoState

			mutations.preventReadOnScroll(state, { value: true })
			expect(state.preventReadOnScroll).toEqual(true)
		})

		it('showAll should update the value in the state', () => {
			const state = { showAll: undefined } as AppInfoState

			mutations.showAll(state, { value: true })
			expect(state.showAll).toEqual(true)
		})

		it('disableRefresh should update the value in the state', () => {
			const state = { disableRefresh: undefined } as AppInfoState

			mutations.disableRefresh(state, { value: true })
			expect(state.disableRefresh).toEqual(true)
		})

		it('starredOpenState should update the value in the state', () => {
			const state = { starredOpenState: undefined } as AppInfoState

			mutations.starredOpenState(state, { value: true })
			expect(state.starredOpenState).toEqual(true)
		})
	})
})
