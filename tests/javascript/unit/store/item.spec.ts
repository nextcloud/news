import axios from '@nextcloud/axios'
import { AppState } from '../../../../src/store'
import { FEED_ITEM_ACTION_TYPES, mutations, actions } from '../../../../src/store/item'

import { FEED_ITEM_MUTATION_TYPES } from '../../../../src/types/MutationTypes'
import { FeedItem } from '../../../../src/types/FeedItem'

jest.mock('@nextcloud/axios')

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		describe('FETCH_STARRED', () => {
			it('should call GET and commit items and starred count to state', async () => {
				(axios as any).get.mockResolvedValue({ data: { items: [], starred: 3 } })
				const commit = jest.fn()
				await (actions[FEED_ITEM_ACTION_TYPES.FETCH_STARRED] as any)({ commit })
				expect(axios.get).toBeCalled()
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, [])
				expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, 3)
			})
		})

		it('MARK_READ should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			await (actions[FEED_ITEM_ACTION_TYPES.MARK_READ] as any)({ commit }, { item })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('MARK_UNREAD should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 }
			const commit = jest.fn()
			await (actions[FEED_ITEM_ACTION_TYPES.MARK_UNREAD] as any)({ commit }, { item })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalledWith(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		})

		it('STAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 };
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })
			const commit = jest.fn()
			await (actions[FEED_ITEM_ACTION_TYPES.STAR_ITEM] as any)({ commit }, { item })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('UNSTAR_ITEM should call GET and commit returned feeds to state', async () => {
			const item = { id: 1 };
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })
			const commit = jest.fn()
			await (actions[FEED_ITEM_ACTION_TYPES.UNSTAR_ITEM] as any)({ commit }, { item })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		describe('SET_ITEMS', () => {
			it('should add feeds to state', () => {
				const state = { allItems: [] as any } as AppState
				let items = [] as any

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(0)

				items = [{ title: 'test' }] as FeedItem[]

				mutations[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state, items)
				expect(state.allItems.length).toEqual(1)
				expect(state.allItems[0]).toEqual(items[0])
			})
		})

		describe('SET_STARRED_COUNT', () => {
			it('should add a single feed to state', () => {
				const state = { } as AppState

				(mutations[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT] as any)(state, 13)
				expect(state.starredCount).toEqual(13)
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
	})
})
