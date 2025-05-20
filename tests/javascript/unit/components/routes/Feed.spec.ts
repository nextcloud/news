import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Feed from '../../../../../src/components/routes/Feed.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Feed.vue', () => {
	'use strict'
	let wrapper: any

	const mockFeed = {
		id: 123,
		title: 'feed name',
		unreadCount: 2,
	}

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
				feeds: () => [mockFeed],
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

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get feed items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
	})

	it('should dispatch FETCH_FEED_ITEMS action if not fetchingItems.feed-123', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems['feed-123'] = false;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_FEED_ITEMS action if fetchingItems.feed-123', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems['feed-123'] = true;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})

	it('should dispatch FEED_MARK_READ action', () => {
		(wrapper.vm as any).markRead()
		expect(store.dispatch).toBeCalled()
	})
})
