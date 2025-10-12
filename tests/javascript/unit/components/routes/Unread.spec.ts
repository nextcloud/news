import { nextTick } from 'vue'
import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Unread from '../../../../../src/components/routes/Unread.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

describe('Unread.vue', () => {
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
			unread: true,
		}, {
			id: 4,
			feedId: 1,
			title: 'feed item 4',
			pubDate: Date.now() / 1000,
			unread: true,
		}
	]

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						unread: false,
					},
					lastItemLoaded: {
						unread: 1,
					},
					newestItemId: {
						number: 12,
					},
					unread: mockItems,
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
				unread: () => mockItems,
				oldestFirst: (state) => state.app.oldestFirst,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Unread, {
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get unread items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(4)
	})

	it('should get only first item from state ordering oldest>newest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.unread = 1;
		(wrapper.vm as any).$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state ordering newest>oldest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.unread = 4;
		(wrapper.vm as any).$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_UNREAD action if not fetchingItems.unread', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.unread = false;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_UNREAD action if fetchingItems.unread', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.unread = true;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})

	it('should clear unread cache when newestItemId resets', () => {
		store.state.items.newestItemId = 0;

		(wrapper.vm as any).$options.watch.newestItemId.call(wrapper.vm, wrapper.vm.newestItemId)
		expect((wrapper.vm as any).unreadCache).toEqual([])
	})
})
