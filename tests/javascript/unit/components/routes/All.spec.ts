import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, describe, expect, it, vi } from 'vitest'

import All from '../../../../../src/components/routes/All.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('All.vue', () => {
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
						all: false,
					},
				},
			},
			actions: {
			},
			getters: {
				allItems: () => [mockItem, mockItem, mockItem],
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(All, {
			props: {
				item: mockItem,
			},
			global: {
				plugins: [store],
			},
		})
	})

	it('should get all items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(3)
	})

	it('should dispatch FETCH_ITEMS action if not fetchingItems.all', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.all = true;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled();

		(wrapper.vm as any).$store.state.items.fetchingItems.all = false;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
