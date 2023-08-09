import axios from '@nextcloud/axios'
import { Feed } from '../../../../src/types/Feed'
import { AppState } from '../../../../src/store'
import { FEED_ACTION_TYPES, mutations, actions } from '../../../../src/store/feed'

import { FEED_MUTATION_TYPES } from '../../../../src/types/MutationTypes'

jest.mock('@nextcloud/axios')

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('ADD_FEED', () => {
			it('should call POST and commit feed to state', async () => {
				(axios as any).post.mockResolvedValue({ data: { feeds: [] } })
				const commit = jest.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit }, { feedReq: { url: '' } })
				expect(axios.post).toBeCalled()
				expect(commit).toBeCalled()
			})

			it('should call POST and not call commit if error', async () => {
				(axios as any).post.mockRejectedValue()
				const commit = jest.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit }, { feedReq: { url: '' } })
				expect(axios.post).toBeCalled()

				expect(commit).not.toBeCalled()
			})
		})

		it('FETCH_FEEDS should call GET and commit returned feeds to state', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })
			const commit = jest.fn()
			await (actions[FEED_ACTION_TYPES.FETCH_FEEDS] as any)({ commit })
			expect(axios.get).toBeCalled()
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		describe('SET_FEEDS', () => {
			it('should add feeds to state', () => {
				const state = { feeds: [] as Feed[], folders: [] as any[] } as AppState
				let feeds = [] as any

				mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
				expect(state.feeds.length).toEqual(0)

				feeds = [{ title: 'test' }] as Feed[]

				mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
				expect(state.feeds.length).toEqual(1)
				expect(state.feeds[0]).toEqual(feeds[0])
			})
		})

		describe('ADD_FEED', () => {
			it('should add a single feed to state', () => {
				const state = { feeds: [] as Feed[], folders: [] as any[] } as AppState
				const feed = { title: 'test' } as any

				mutations[FEED_MUTATION_TYPES.ADD_FEED](state, feed)
				expect(state.feeds.length).toEqual(1)
				expect(state.feeds[0]).toEqual(feed)
			})
		})
	})
})
