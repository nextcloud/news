import { Feed } from '../../../../src/types/Feed'
import { AppState } from '../../../../src/store'
import { FEED_ACTION_TYPES, mutations, actions } from '../../../../src/store/feed'
import { FeedService } from '../../../../src/dataservices/feed.service'

import { FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES } from '../../../../src/types/MutationTypes'

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('FETCH_FEEDS', () => {
			it('should call FeedService.fetchAllFeeds and commit returned feeds to state', async () => {
				FeedService.fetchAllFeeds = jest.fn();
				(FeedService.fetchAllFeeds as any).mockResolvedValue({ data: { feeds: [] } })
				const commit = jest.fn()
				await (actions[FEED_ACTION_TYPES.FETCH_FEEDS] as any)({ commit })
				expect(FeedService.fetchAllFeeds).toBeCalled()
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.SET_FEEDS, [])
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, 0)
			})
		})

		describe('ADD_FEED', () => {
			it('should call FeedService.addFeed and commit feed to state', async () => {
				FeedService.addFeed = jest.fn();
				(FeedService.addFeed as any).mockResolvedValue({ data: { feeds: [] } })
				const commit = jest.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit } as any, { feedReq: { url: '' } } as any)
				expect(FeedService.addFeed).toBeCalled()
				expect(commit).toBeCalled()
			})

			it('should call FeedService.addFeed and not call commit if error', async () => {
				FeedService.addFeed = jest.fn();
				(FeedService.addFeed as any).mockRejectedValue()
				const commit = jest.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit } as any, { feedReq: { url: '' } } as any)
				expect(FeedService.addFeed).toBeCalled()

				expect(commit).not.toBeCalled()
			})
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
