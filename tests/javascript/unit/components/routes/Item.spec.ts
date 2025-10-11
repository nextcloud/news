import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Item from '../../../../../src/components/routes/Item.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Item.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 123,
		}
	]

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						all: false,
					},
					lastItemLoaded: {
						all: 1,
					},
					allItems: mockItems,
				},
				feeds: {
				},
				app: {
					oldestFirst: false,
				},
			},
			actions: {
			},
			getters: {
				allItems: () => mockItems,
				oldestFirst: (state) => state.app.oldestFirst,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Item, {
			props: {
				itemId: '123',
			},
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should show item with id 123', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items[0].id).toEqual(123)
	})

	it('should dispatch FETCH_ITEMS action if not fetchingItems.all', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.all = false;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_ITEMS action if fetchingItems.all', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.all = true;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})
})
