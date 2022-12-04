import axios from '@nextcloud/axios'
import { Feed } from '../../../../src/types/Feed'
import { AppState } from '../../../../src/store'
import { FEED_ACTION_TYPES, FEED_MUTATION_TYPES, mutations, actions } from '../../../../src/store/feed'

jest.mock('@nextcloud/axios')

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		it('ADD_FEED should call POST and commit feed to state', async () => {
			(axios as any).post.mockResolvedValue()
			const commit = jest.fn()
			await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit }, { feedReq: { url: '' } })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
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
		it('SET_FEEDS should add feeds to state', () => {
			const state = { feeds: [] as Feed[], folders: [] as any[] } as AppState
			let feeds = [] as Feed[]
			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)

			expect(state.feeds.length).toEqual(0)

			feeds = [{ title: 'test' }] as Feed[]
			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)

			expect(state.feeds.length).toEqual(1)
			expect(state.feeds[0]).toEqual(feeds[0])
		})

		it('SET_FEEDS should add feeds and unreadCount to folder if exists and folder set', () => {
			const state = { feeds: [] as Feed[], folders: [{ id: 1, feedCount: 3, feeds: [] as Feed[] }] } as AppState
			const feeds = [{ title: 'test', folderId: 1, unreadCount: 2 }] as Feed[]

			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)

			expect(state.feeds.length).toEqual(1)
			expect(state.feeds[0]).toEqual(feeds[0])
			expect(state.folders[0].feeds[0]).toEqual(feeds[0])
			expect(state.folders[0].feedCount).toEqual(5)
		})
	})
})
