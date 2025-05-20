import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Unread from '../../../../../src/components/routes/Unread.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Unread.vue', () => {
	'use strict'
	let wrapper: any

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
	}

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						unread: false,
					},
					newestItemId: {
						number: 12,
					},
				},
			},
			actions: {
			},
			getters: {
				unread: () => [mockItem, mockItem],
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Unread, {
			props: {
				item: mockItem,
			},
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get unread items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
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
