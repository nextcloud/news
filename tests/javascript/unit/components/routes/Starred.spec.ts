import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, describe, expect, it, vi } from 'vitest'

import Starred from '../../../../../src/components/routes/Starred.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Starred.vue', () => {
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
						starred: false,
					},
				},
			},
			actions: {
			},
			getters: {
				starred: () => [mockItem],
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Starred, {
			props: {
				item: mockItem,
			},
			global: {
				plugins: [store],
			},
		})
	})

	it('should get starred items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_STARRED action if not fetchingItems.starred', () => {
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
