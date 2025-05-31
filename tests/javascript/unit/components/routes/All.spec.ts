import { nextTick } from 'vue'
import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import All from '../../../../../src/components/routes/All.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('All.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 1,
			feedId: 1,
			title: 'feed item',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 2,
			feedId: 1,
			title: 'feed item 2',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 3,
			feedId: 1,
			title: 'feed item 3',
			pubDate: Date.now() / 1000,
		}, {
			id: 4,
			feedId: 1,
			title: 'feed item 4',
			pubDate: Date.now() / 1000,
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

		wrapper = shallowMount(All, {
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get all items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(4)
	})

	it('should get only first item from state ordering oldest>newest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.all = 1;
		(wrapper.vm as any).$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state ordering newest>oldest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.all = 4;
		(wrapper.vm as any).$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
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
