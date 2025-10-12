import type { AppState } from '../../../../src/store/index.ts'
import type { Feed } from '../../../../src/types/Feed.ts'

import { describe, expect, it, vi } from 'vitest'
import { FeedService } from '../../../../src/dataservices/feed.service'
import { ItemService } from '../../../../src/dataservices/item.service'
import { FEED_ORDER, FEED_UPDATE_MODE } from '../../../../src/enums/index.ts'
import { actions, FEED_ACTION_TYPES, mutations } from '../../../../src/store/feed.ts'
import { FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES, FOLDER_MUTATION_TYPES } from '../../../../src/types/MutationTypes.ts'

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('FETCH_FEEDS', () => {
			it('should call FeedService.fetchAllFeeds and commit returned feeds to state', async () => {
				FeedService.fetchAllFeeds = vi.fn();
				(FeedService.fetchAllFeeds as any).mockResolvedValue({ data: { feeds: [] } })
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FETCH_FEEDS] as any)({ commit })
				expect(FeedService.fetchAllFeeds).toBeCalled()
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.SET_FEEDS, [])
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, 0)
			})
		})

		describe('ADD_FEED', () => {
			it('should call FeedService.addFeed and commit feed to state', async () => {
				FeedService.addFeed = vi.fn();
				(FeedService.addFeed as any).mockResolvedValue({ data: { feeds: [] } })
				const commit = vi.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit } as any, { feedReq: { url: '' } } as any)
				expect(FeedService.addFeed).toBeCalled()
				expect(commit).toBeCalled()
			})

			it('should call FeedService.addFeed and not call commit if error', async () => {
				FeedService.addFeed = vi.fn();
				(FeedService.addFeed as any).mockRejectedValue()
				const commit = vi.fn()
				await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit } as any, { feedReq: { url: '' } } as any)
				expect(FeedService.addFeed).toBeCalled()

				expect(commit).not.toBeCalled()
			})
		})

		describe('FEED_MARK_READ', () => {
			it('should call FeedService.markRead and commit all items read to state', async () => {
				ItemService.fetchFeedItems = vi.fn();
				(ItemService.fetchFeedItems as any).mockResolvedValue({ data: { items: [{ id: 123 }] } })
				FeedService.markRead = vi.fn()
				const commit = vi.fn()
				const feed = { id: 1, title: 'feed' }

				await (actions[FEED_ACTION_TYPES.FEED_MARK_READ] as any)({ commit }, { feed })
				expect(FeedService.markRead).toBeCalled()
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.SET_FEED_ALL_READ, feed)
			})

			it('should commit MODIFY_FOLDER_UNREAD_COUNT with feed unreadCount if folderId exists on feed ', async () => {
				ItemService.fetchFeedItems = vi.fn();
				(ItemService.fetchFeedItems as any).mockResolvedValue({ data: { items: [{ id: 123 }] } })
				FeedService.markRead = vi.fn()
				const commit = vi.fn()
				const feed = { id: 1, title: 'feed', folderId: 234, unreadCount: 2 }

				await (actions[FEED_ACTION_TYPES.FEED_MARK_READ] as any)({ commit }, { feed })
				expect(FeedService.markRead).toBeCalled()
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.SET_FEED_ALL_READ, feed)
				expect(commit).toBeCalledWith(FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT, { folderId: 234, delta: -2 })
			})
		})

		describe('FEED_SET_PINNED', () => {
			it('should call FeedService.updateFeed and commit updated `pinned` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_PINNED] as any)({ commit }, { feed: { id: 1 }, pinned: true })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, pinned: true })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, pinned: true })
			})
		})

		describe('FEED_SET_PREVENT_UPDATE', () => {
			it('should call FeedService.updateFeed and commit updated `preventUpdate` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_PREVENT_UPDATE] as any)({ commit }, { feed: { id: 1 }, preventUpdate: true })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, preventUpdate: true })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, preventUpdate: true })
			})
		})

		describe('FEED_SET_ORDERING', () => {
			it('should call FeedService.updateFeed and commit updated `ordering` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_ORDERING] as any)({ commit }, { feed: { id: 1 }, ordering: FEED_ORDER.DEFAULT })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, ordering: FEED_ORDER.DEFAULT })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, ordering: FEED_ORDER.DEFAULT })
			})
		})

		describe('FEED_SET_FULL_TEXT', () => {
			it('should call FeedService.updateFeed and commit updated `fullTextEnabled` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_FULL_TEXT] as any)({ commit }, { feed: { id: 1 }, fullTextEnabled: true })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, fullTextEnabled: true })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, fullTextEnabled: true })
			})
		})

		describe('FEED_SET_UPDATE_MODE', () => {
			it('should call FeedService.updateFeed and commit updated `updateMode` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_UPDATE_MODE] as any)({ commit }, { feed: { id: 1 }, updateMode: FEED_UPDATE_MODE.IGNORE })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, updateMode: FEED_UPDATE_MODE.IGNORE })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, updateMode: FEED_UPDATE_MODE.IGNORE })
			})
		})

		describe('FEED_SET_TITLE', () => {
			it('should call FeedService.updateFeed and commit updated `title` property to state', async () => {
				FeedService.updateFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_SET_TITLE] as any)({ commit }, { feed: { id: 1 }, title: 'newTitle' })
				expect(FeedService.updateFeed).toBeCalledWith({ feedId: 1, title: 'newTitle' })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.UPDATE_FEED, { id: 1, title: 'newTitle' })
			})
		})

		describe('FEED_DELETE', () => {
			it('should call FeedService.deleteFeed and commit to state', async () => {
				FeedService.deleteFeed = vi.fn()
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.FEED_DELETE] as any)({ commit }, { feed: { id: 1 } })
				expect(FeedService.deleteFeed).toBeCalledWith({ feedId: 1 })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.FEED_DELETE, 1)
			})
		})

		describe('MODIFY_FEED_UNREAD_COUNT', () => {
			const state = {
				feeds: [{ id: 1 }],
			}
			it('should commit to state', async () => {
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.MODIFY_FEED_UNREAD_COUNT] as any)({ commit, state }, { feedId: 1, delta: -2 })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: 1, delta: -2 })
			})

			it('should commit folder to state if feed has folderId', async () => {
				const state = {
					feeds: [{ id: 1, folderId: 234 }],
				}
				const commit = vi.fn()
				await (actions[FEED_ACTION_TYPES.MODIFY_FEED_UNREAD_COUNT] as any)({ commit, state }, { feedId: 1, delta: -2 })
				expect(commit).toBeCalledWith(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: 1, delta: -2 })
				expect(commit).toBeCalledWith(FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT, { folderId: 234, delta: -2 })
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

			it('should sort feeds case insensitive by title', () => {
				const state = { feeds: [] as Feed[], folders: [] as any[] } as AppState
				const feeds = [{ title: 'gamma' }, { title: 'alpha' }, { title: 'Beta' }] as Feed[]

				mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
				expect(state.feeds.length).toEqual(3)
				expect(state.feeds[0].title).toEqual('alpha')
				expect(state.feeds[1].title).toEqual('Beta')
				expect(state.feeds[2].title).toEqual('gamma')
			})

			it('should set feed ordering when set', () => {
				const state = { feeds: [] as Feed[], ordering: { 'feed-0': 0 } } as AppState
				const feeds = [{ id: 0, title: 'test', ordering: 2 }] as Feed[]

				mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
				expect(state.ordering['feed-0']).toEqual(2)
			})

			it('should convert unread count to number', () => {
				const state = { feeds: [] as Feed[], folders: [] as any[] } as AppState
				const feeds = [{ title: 'test', unreadCount: '10' }] as Feed[]

				mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
				expect(state.feeds[0].unreadCount).toEqual(10)
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

		describe('UPDATE_FEED', () => {
			it('should update a feed in the state', () => {
				const state = { feeds: [{ title: 'oldName', id: 1 }] as Feed[], folders: [] as any[] } as AppState
				const feed = { title: 'test', id: 1 } as any

				mutations[FEED_MUTATION_TYPES.UPDATE_FEED](state, feed)
				expect(state.feeds[0].title).toEqual('test')
			})
		})

		describe('SET_NEWEST_ITEM_ID', () => {
			it('should update newestItemId in state', () => {
				const state = { newestItemId: 0 } as AppState

				mutations[FEED_MUTATION_TYPES.SET_NEWEST_ITEM_ID](state, 123)
				expect(state.newestItemId).toEqual(123)
			})
		})

		describe('SET_FEED_ALL_READ', () => {
			it('should update a feed unreadCount to 0 in the state', () => {
				const state = { feeds: [{ title: 'oldName', id: 1, unreadCount: 4 }] as Feed[], folders: [] as any[] } as AppState
				const feed = { title: 'test', id: 1 } as any

				mutations[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](state, feed)
				expect(state.feeds[0].unreadCount).toEqual(0)
			})
		})

		describe('MODIFY_FEED_UNREAD_COUNT', () => {
			it('should update a feed unreadCount to 0 in the state', () => {
				const state = { feeds: [{ title: 'oldName', id: 1, unreadCount: 4 }] as Feed[], folders: [] as any[] } as AppState

				mutations[FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT](state, { feedId: 1, delta: -1 } as any)
				expect(state.feeds[0].unreadCount).toEqual(3)
			})
		})

		describe('FEED_DELETE', () => {
			it('should update a feed unreadCount to 0 in the state', () => {
				const state = { feeds: [{ title: 'oldName', id: 1, unreadCount: 4 }] as Feed[], folders: [] as any[] } as AppState

				mutations[FEED_MUTATION_TYPES.FEED_DELETE](state, 1 as any)
				expect(state.feeds.length).toEqual(0)
			})
		})
	})
})
