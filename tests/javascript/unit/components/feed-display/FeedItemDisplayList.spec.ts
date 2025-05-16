import Vuex, { Store } from 'vuex'
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils'
import { beforeAll, describe, expect, it, vi } from 'vitest'

import FeedItemDisplayList from '../../../../../src/components/feed-display/FeedItemDisplayList.vue'
import VirtualScroll from '../../../../../src/components/feed-display/VirtualScroll.vue'
import FeedItemRow from '../../../../../src/components/feed-display/FeedItemRow.vue'

vi.mock('@nextcloud/axios')

describe('FeedItemDisplayList.vue', () => {
	'use strict'
	let wrapper: any

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
		unread: true,
	}
        const mockFeed = {
                id: 1,
        }

	let store: Store<any>
	beforeAll(() => {
		HTMLElement.prototype.getBoundingClientRect = vi.fn(() => ({
			width: 500,
			height: 500,
			top: 0,
			left: 0,
			bottom: 500,
			right: 500
		}))

		store = new Vuex.Store({
			state: {
				items: {
					allItemsLoaded: {
						unread: false,
						all: false,
						'feed-1': false,
					},
					lastItemLoaded: {
						unread: 0,
						all: 0,
						'feed-1': 0,
					},
					fetchingItems: {
						unread: false,
						all: false,
						'feed-1': false,
					},
					syncNeeded: false,
				},
				feeds: {
					ordering: {
						'feed-1': 0,
					},
				},
			},
			actions: {
			},
			getters: {
				feeds: () => [mockFeed],
				unread: () => [mockItem, mockItem],
				showAll: () => { return true },
				oldestFirst: () => { return true },
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()


	})

	it('should create FeedItemRow items from input', async () => {
		wrapper = mount(FeedItemDisplayList, {
			attachTo: document.body,
			props: {
				items: [mockItem],
				fetchKey: 'unread',
			},
			global: {
				plugins: [store],
				stubs: {
					VirtualScroll: false
				},
			},
		})

		// make sure dom elements are mounted properly
		await nextTick()
		await nextTick()

		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(1)

		// add another unread item
		await wrapper.setProps({
			items: [mockItem, mockItem],
			fetchKey: 'unread',
		})

		// make sure dom elements are mounted properly
		await nextTick()
		await nextTick()

		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(2)

		// switch route to all with 1 item
		await wrapper.setProps({
			items: [mockItem],
			fetchKey: 'all',
		})

		// make sure dom elements are mounted properly
		await nextTick()
		await nextTick()

		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(1)

		// switch route to feed-1 with 3 items
		await wrapper.setProps({
			items: [mockItem, mockItem, mockItem],
			fetchKey: 'feed-1',
		})

		// make sure dom elements are mounted properly
		await nextTick()
		await nextTick()

		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(3)
	})

})
