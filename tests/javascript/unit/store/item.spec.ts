import { AppState } from '../../../../src/store'
import { FEED_ITEM_ACTION_TYPES, mutations, actions } from '../../../../src/store/item'

import { FEED_ITEM_MUTATION_TYPES } from '../../../../src/types/MutationTypes'
import { ItemService } from '../../../../src/dataservices/item.service'

describe('item.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('FETCH_UNREAD', () => {
			it('should call ItemService and commit items to state', async () => {
				const fetchMock = jest.fn()
				fetchMock.mockResolvedValue({ data: { items: [] } })
				ItemService.debounceFetchUnread = fetchMock as any
				const commit = jest.fn()

				await (actions[FEED_ITEM_ACTION_TYPES.FETCH_UNREAD] as any)({ commit })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [])
			})
		})

		describe('FETCH_STARRED', () => {
			it('should call ItemService and commit items and starred count to state', async () => {
				const fetchMock = jest.fn()
				fetchMock.mockResolvedValue({ data: { items: [], starred: 3 } })
				ItemService.debounceFetchStarred = fetchMock as any
				const commit = jest.fn()

				await (actions[FEED_ITEM_ACTION_TYPES.FETCH_STARRED] as any)({ commit })

				expect(fetchMock).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [])
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, 3)
			})
		})

		it('MARK_READ should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			const serviceMock = jest.fn()
			ItemService.markRead = serviceMock

			await (actions[FEED_ITEM_ACTION_TYPES.MARK_READ] as any)({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, true)
			expect(commit).toBeCalled()
		})

		it('MARK_UNREAD should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			const serviceMock = jest.fn()
			ItemService.markRead = serviceMock

			await (actions[FEED_ITEM_ACTION_TYPES.MARK_UNREAD] as any)({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, false)
			expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		})

		it('STAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			const serviceMock = jest.fn()
			ItemService.markStarred = serviceMock

			await (actions[FEED_ITEM_ACTION_TYPES.STAR_ITEM] as any)({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, true)
			expect(commit).toBeCalled()
		})

		it('UNSTAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			const serviceMock = jest.fn()
			ItemService.markStarred = serviceMock

			await (actions[FEED_ITEM_ACTION_TYPES.UNSTAR_ITEM] as any)({ commit }, { item })

			expect(serviceMock).toBeCalledWith(item, false)
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		describe('SET_SELECTED_ITEM', () => {
			it('should update selectedId on state', async () => {
				const state = { selectedId: undefined } as any
				const item = { id: 123 } as any
				mutations[FEED_ITEM_MUTATION_TYPES.SET_SELECTED_ITEM](state, item as any)
				expect(state.selectedId).toEqual(123)
			})
		})
		describe('SET_ITEMS', () => {
			it('should add feeds to state', () => {
				const state = { allItems: [] as any } as any
				let items = [] as any

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
				const state = { allItems: [] as any } as any
				let items = [{ title: 'test', id: 123 }] as any

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
		})

		describe('SET_STARRED_COUNT', () => {
			it('should add a single feed to state', () => {
				const state = { } as AppState

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT] as any)(state, 13)
				expect(state.starredCount).toEqual(13)
			})
		})

		describe('SET_UNREAD_COUNT', () => {
			it('should set unreadCount with value passed in', () => {
				const state = { unreadCount: 0 } as AppState

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT] as any)(state, 123)
				expect(state.unreadCount).toEqual(123)
			})
		})

		describe('UPDATE_ITEM', () => {
			it('should add a single feed to state', () => {
				const state = { allItems: [{ id: 1, title: 'abc' }] as any } as AppState
				const item = { title: 'test', id: 1 } as any

				(mutations[FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM] as any)(state, { item })
				expect(state.allItems[0]).toEqual(item)
			})
		})

		describe('SET_FETCHING', () => {
			it('should set fetchingItems value with key passed in', () => {
				const state = { fetchingItems: {} } as AppState

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_FETCHING] as any)(state, { fetching: true, key: 'starred' })
				expect(state.fetchingItems.starred).toEqual(true);

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_FETCHING] as any)(state, { fetching: false, key: 'starred' })
				expect(state.fetchingItems.starred).toEqual(false)
			})
		})

		describe('SET_ALL_LOADED', () => {
			it('should set allItemsLoaded value with key passed in', () => {
				const state = { allItemsLoaded: {} } as AppState

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED] as any)(state, { loaded: true, key: 'starred' })
				expect(state.allItemsLoaded.starred).toEqual(true);

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED] as any)(state, { loaded: false, key: 'starred' })
				expect(state.allItemsLoaded.starred).toEqual(false)
			})
		})
	})
})
