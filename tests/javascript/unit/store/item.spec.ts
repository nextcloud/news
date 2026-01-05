import type { AppState } from '../../../../src/store/index.ts'

import { describe, expect, it, vi } from 'vitest'
import { ItemService } from '../../../../src/dataservices/item.service'
import { actions, FEED_ITEM_ACTION_TYPES, mutations } from '../../../../src/store/item.ts'
import { FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES } from '../../../../src/types/MutationTypes.ts'

describe('item.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('FETCH_UNREAD', () => {
			it('should call ItemService and commit items to state', async () => {
				const fetchMock = vi.fn()
				fetchMock.mockResolvedValue({ data: { items: [{ id: 123 }] } })
				ItemService.fetchUnread = fetchMock
				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_UNREAD]({ commit })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [{ id: 123 }])
			})
		})

		describe('FETCH_ITEMS', () => {
			it('should call ItemService and commit items to state', async () => {
				const fetchMock = vi.fn()
				fetchMock.mockResolvedValue({ data: { items: [{ id: 123 }] } })
				ItemService.fetchAll = fetchMock
				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_ITEMS]({ commit })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [{ id: 123 }])
			})
		})

		describe('FETCH_STARRED', () => {
			it('should call ItemService and commit items and starred count to state', async () => {
				const fetchMock = vi.fn()
				fetchMock.mockResolvedValue({ data: { items: [{ id: 123 }], starred: 3 } })
				ItemService.fetchStarred = fetchMock
				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_STARRED]({ commit })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [{ id: 123 }])
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, 3)
			})

			it('commits items, starred count, all loaded true and last item when items.length < 40 and starred present', async () => {
				const feedId = 42
				const items = [{ id: 1 }, { id: 2 }]
				// mock response with starred count and newestItemId
				vi.spyOn(ItemService, 'fetchStarred').mockResolvedValue({
					data: {
						items,
						starred: 5,
						newestItemId: 999,
					},
				} as any)

				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_STARRED]({ commit } as any, { feedId, start: 0 } as any)

				// first commit: set fetching true
				expect(commit.mock.calls[0][0]).toBe(FEED_ITEM_MUTATION_TYPES.SET_FETCHING)
				expect(commit.mock.calls[0][1]).toEqual({ key: 'starred-' + feedId, fetching: true })

				// SET_ITEMS
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, items)

				// SET_STARRED_COUNT should be called with the starred value
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, 5)

				// items.length < 40 -> SET_ALL_LOADED true
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'starred-' + feedId, loaded: true })

				// last item set to last item's id
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'starred-' + feedId, lastItem: 2 })

				// final commit: set fetching false
				expect(commit.mock.calls[commit.mock.calls.length - 1][0]).toBe(FEED_ITEM_MUTATION_TYPES.SET_FETCHING)
				expect(commit.mock.calls[commit.mock.calls.length - 1][1]).toEqual({ key: 'starred-' + feedId, fetching: false })

				vi.restoreAllMocks()
			})

			it('commits all loaded false when items.length >= 40 and does not call SET_STARRED_COUNT if missing', async () => {
				const feedId = 7
				// create 40 items
				const items = Array.from({ length: 40 }, (_, i) => ({ id: i + 1 }))
				vi.spyOn(ItemService, 'fetchStarred').mockResolvedValue({
					data: {
						items,
						// no 'starred' property in this response
					},
				} as any)

				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_STARRED]({ commit } as any, { feedId, start: 0 } as any)

				// SET_ITEMS called
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, items)

				// SET_STARRED_COUNT should NOT be called
				const calledStarred = commit.mock.calls.some((c) => c[0] === FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT)
				expect(calledStarred).toBe(false)

				// items.length >= 40 -> SET_ALL_LOADED false
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'starred-' + feedId, loaded: false })

				// lastItem should be the 40th id
				expect(commit).toHaveBeenCalledWith(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'starred-' + feedId, lastItem: 40 })

				vi.restoreAllMocks()
			})
		})

		describe('FETCH_FEED_ITEMS', () => {
			it('should call ItemService and commit items to state', async () => {
				const mockItems = [{ id: 123, title: 'feed item' }]
				const fetchMock = vi.fn()
				fetchMock.mockResolvedValue({ data: { items: mockItems } })
				ItemService.fetchFeedItems = fetchMock
				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_FEED_ITEMS]({ commit }, { feedId: 123 })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, mockItems)
			})
		})

		describe('FETCH_FOLDER_FEED_ITEMS', () => {
			it('should call ItemService and commit items to state', async () => {
				const mockItems = [{ id: 123, title: 'feed item' }]
				const fetchMock = vi.fn()
				fetchMock.mockResolvedValue({ data: { items: mockItems } })
				ItemService.fetchFolderItems = fetchMock
				const commit = vi.fn()

				await actions[FEED_ITEM_ACTION_TYPES.FETCH_FOLDER_FEED_ITEMS]({ commit }, { feedId: 123 })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, mockItems)
			})
		})

		it('MARK_READ should call GET and commit returned feeds to state', async () => {
			const item = { id: 1, feedId: 123, unread: true }
			const commit = vi.fn()
			const dispatch = vi.fn()
			const serviceMock = vi.fn()
			ItemService.markRead = serviceMock

			await actions[FEED_ITEM_ACTION_TYPES.MARK_READ]({ commit, dispatch }, { item })

			expect(serviceMock).toBeCalledWith(item, true)
			expect(commit).toBeCalled()
			expect(dispatch).toBeCalledWith(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: 123, delta: -1 })
		})

		it('MARK_UNREAD should call GET and commit returned feeds to state', async () => {
			const item = { id: 1, feedId: 123 }
			const commit = vi.fn()
			const dispatch = vi.fn()
			const serviceMock = vi.fn()
			ItemService.markRead = serviceMock

			await actions[FEED_ITEM_ACTION_TYPES.MARK_UNREAD]({ commit, dispatch }, { item })

			expect(serviceMock).toBeCalledWith(item, false)
			expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
			expect(dispatch).toBeCalledWith(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: 123, delta: 1 })
		})

		it('STAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = vi.fn()
			const serviceMock = vi.fn()
			ItemService.markStarred = serviceMock

			await actions[FEED_ITEM_ACTION_TYPES.STAR_ITEM]({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, true)
			expect(commit).toBeCalled()
		})

		it('UNSTAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = vi.fn()
			const serviceMock = vi.fn()
			ItemService.markStarred = serviceMock

			await actions[FEED_ITEM_ACTION_TYPES.UNSTAR_ITEM]({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, false)
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		describe('SET_SELECTED_ITEM', () => {
			it('should update selectedId on state', async () => {
				const state = { selectedId: undefined, recentItemIds: [] }
				const item = { id: 123 }
				mutations[FEED_ITEM_MUTATION_TYPES.SET_SELECTED_ITEM](state, item)
				expect(state.selectedId).toEqual(123)
			})
		})

		describe('SET_PLAYING_ITEM', () => {
			it('should update selectedId on state', async () => {
				const state = { playingItem: undefined }
				const item = { id: 123 }
				mutations[FEED_ITEM_MUTATION_TYPES.SET_PLAYING_ITEM](state, item)
				expect(state.playingItem).toEqual(item)
			})
		})

		describe('SET_ITEMS', () => {
			it('should add feeds to state', () => {
				const state = { allItems: [] }
				let items = []

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(0)

				items = [{ title: 'test', id: 123 }]

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(1)
				expect(state.allItems[0]).toEqual(items[0])

				items = [{ title: 'test2', id: 234 }]
				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(2)
			})

			it('should not add duplicates', () => {
				const state = { allItems: [] }
				let items = [{ title: 'test', id: 123 }]

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(1)
				expect(state.allItems[0]).toEqual(items[0])

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(1)
				expect(state.allItems[0]).toEqual(items[0])

				items = [{ title: 'test2', id: 234 }]
				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(2)
			})

			it('should set syncNeeded flag when newestItemId changed', () => {
				const state = { allItems: [], newestItemId: 0 }
				const items = [{ title: 'test', id: 123 }]

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.syncNeeded).toEqual(true)
			})

			it('should set title from url if title is missing', () => {
				const state = { allItems: [] }
				const items = [{ title: '', url: 'https://feedurl', id: 123 }]

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems[0].title).toEqual('https://feedurl')
			})
		})

		describe('SET_STARRED_COUNT', () => {
			it('should add a single feed to state', () => {
				const state = { } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT](state, 13)
				expect(state.starredCount).toEqual(13)
			})
		})

		describe('SET_UNREAD_COUNT', () => {
			it('should set unreadCount with value passed in', () => {
				const state = { unreadCount: 0 } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT](state, 123)
				expect(state.unreadCount).toEqual(123)
			})
		})

		describe('MODIFY_UNREAD_COUNT', () => {
			it('should modify unreadCount with value passed in', () => {
				const state = { unreadCount: 123 } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.MODIFY_UNREAD_COUNT](state, { delta: 5 })
				expect(state.unreadCount).toEqual(128)

				mutations[FEED_ITEM_MUTATION_TYPES.MODIFY_UNREAD_COUNT](state, { delta: -3 })
				expect(state.unreadCount).toEqual(125)
			})
		})

		describe('UPDATE_ITEM', () => {
			it('should add a single feed to state', () => {
				const state = { allItems: [{ id: 1, title: 'abc' }] } as AppState
				const item = { title: 'test', id: 1 }

				mutations[FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM](state, { item })
				expect(state.allItems[0]).toEqual(item)
			})
		})

		describe('SET_FETCHING', () => {
			it('should set fetchingItems value with key passed in', () => {
				const state = { fetchingItems: {} } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_FETCHING](state, { fetching: true, key: 'starred' })
				expect(state.fetchingItems.starred).toEqual(true)

				mutations[FEED_ITEM_MUTATION_TYPES.SET_FETCHING](state, { fetching: false, key: 'starred' })
				expect(state.fetchingItems.starred).toEqual(false)
			})
		})

		describe('SET_ALL_LOADED', () => {
			it('should set allItemsLoaded value with key passed in', () => {
				const state = { allItemsLoaded: {} } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED](state, { loaded: true, key: 'starred' })
				expect(state.allItemsLoaded.starred).toEqual(true)

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED](state, { loaded: false, key: 'starred' })
				expect(state.allItemsLoaded.starred).toEqual(false)
			})
		})

		describe('SET_LAST_ITEM_LOADED', () => {
			it('should set lastItemLoaded value with key passed in', () => {
				const state = { lastItemLoaded: {} } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED](state, { lastItem: 123, key: 'unread' })
				expect(state.lastItemLoaded.unread).toEqual(123)
			})
		})

		describe('SET_NEWEST_ITEM_ID', () => {
			it('should set newestItemId and reset allItemsLoaded values', () => {
				const state = { newestItemId: 123, allItemsLoaded: { unread: true } } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.SET_NEWEST_ITEM_ID](state, 1234)
				expect(state.allItemsLoaded.unread).toEqual(undefined)
				expect(state.newestItemId).toEqual(1234)
			})
		})

		describe('RESET_ITEM_STATES', () => {
			it('should reset item states', () => {
				const state = { allItems: [{ id: 1, title: 'abc' }] } as AppState

				mutations[FEED_ITEM_MUTATION_TYPES.RESET_ITEM_STATES](state)
				expect(state.allItems.length).toEqual(0)
			})
		})

		describe('SET_FEED_ALL_READ', () => {
			it('should set allItems with feedId as read', () => {
				const state = { allItems: [{ id: 1, feedId: 123, unread: true }, { id: 2, feedId: 345, unread: true }] }

				mutations[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](state, { id: 123 })
				expect(state.allItems[0].unread).toEqual(false)

				mutations[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](state, { id: 345 })
				expect(state.allItems[1].unread).toEqual(false)
			})
		})
	})

})
