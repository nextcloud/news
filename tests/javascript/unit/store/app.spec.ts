import type { AppInfoState } from '../../../../src/store/app'

import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createStore } from 'vuex'
import { DISPLAY_MODE, SPLIT_MODE } from '../../../../src/enums/index.ts'
import appInfo from '../../../../src/store/app'
import { mutations } from '../../../../src/store/app'
import { APPLICATION_MUTATION_TYPES } from '../../../../src/types/MutationTypes'

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
	})

	// describe('actions', () => {

	// })

	describe('mutations', () => {
		it('SET_ERROR should update the error in the state', () => {
			const state = { error: undefined } as AppInfoState

			const error = { message: 'test err' };

			(mutations[APPLICATION_MUTATION_TYPES.SET_ERROR] as any)(state, error)
			expect(state.error).toEqual(error);

			(mutations[APPLICATION_MUTATION_TYPES.SET_ERROR] as any)(state, undefined)
			expect(state.error).toEqual(undefined)
		})

		it('SET_LOADING should update loading flag in the state', () => {
			const state = { loading: undefined } as AppInfoState

			(mutations[APPLICATION_MUTATION_TYPES.SET_LOADING] as any)(state, { value: true })
			expect(state.loading).toEqual(true);

			(mutations[APPLICATION_MUTATION_TYPES.SET_LOADING] as any)(state, { value: false })
			expect(state.loading).toEqual(false)
		})

		it('displaymode should update the value in the state', () => {
			const state = { displaymode: undefined } as AppInfoState

			(mutations.displaymode as any)(state, { value: DISPLAY_MODE.DEFAULT })
			expect(state.displaymode).toEqual(DISPLAY_MODE.DEFAULT)
		})

		it('splitmode should update the value in the state', () => {
			const state = { splitmode: undefined } as AppInfoState

			(mutations.splitmode as any)(state, { value: SPLIT_MODE.OFF })
			expect(state.splitmode).toEqual(SPLIT_MODE.OFF)
		})

		it('oldestFirst should update the value in the state', () => {
			const state = { oldestFirst: undefined } as AppInfoState

			(mutations.oldestFirst as any)(state, { value: true })
			expect(state.oldestFirst).toEqual(true)
		})

		it('preventReadOnScroll should update the value in the state', () => {
			const state = { preventReadOnScroll: undefined } as AppInfoState

			(mutations.preventReadOnScroll as any)(state, { value: true })
			expect(state.preventReadOnScroll).toEqual(true)
		})

		it('showAll should update the value in the state', () => {
			const state = { showAll: undefined } as AppInfoState

			(mutations.showAll as any)(state, { value: true })
			expect(state.showAll).toEqual(true)
		})

		it('disableRefresh should update the value in the state', () => {
			const state = { disableRefresh: undefined } as AppInfoState

			(mutations.disableRefresh as any)(state, { value: true })
			expect(state.disableRefresh).toEqual(true)
		})
	})
})
