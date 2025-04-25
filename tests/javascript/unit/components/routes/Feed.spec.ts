import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, describe, expect, it, vi } from 'vitest'

import Feed from '../../../../../src/components/routes/Feed.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Feed.vue', () => {
	'use strict'
	let wrapper: any

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						'feed-123': false,
					},
					allItems: [{
						feedId: 123,
						title: 'feed item',
					}, {
						feedId: 123,
						title: 'feed item 2',
					}],
				},
			},
			actions: {
			},
			getters: {
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Feed, {
			props: {
				feedId: '123',
			},
			global: {
				mocks: {
					$route: {
						params: {},
					},
				},
				plugins: [store],
			},
		})
	})

	it('should get starred items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
	})

	it('should dispatch FETCH_FEED_ITEMS action if not fetchingItems.starred', () => {
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
