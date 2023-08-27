import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import Feed from '../../../../../src/components/routes/Feed.vue'
import FeedItemDisplayList from '../../../../../src/components/FeedItemDisplayList.vue'

jest.mock('@nextcloud/axios')

describe('Feed.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	localVue.use(Vuex)
	let wrapper: Wrapper<Feed>

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

		store.dispatch = jest.fn()
		store.commit = jest.fn()

		wrapper = shallowMount(Feed, {
			propsData: {
				feedId: '123',
			},
			mocks: {
				$route: {
					params: {},
				},
			},
			localVue,
			store,
		})
	})

	it('should display feed title and unread count', () => {
		expect(wrapper.find('.header').text()).toContain(mockFeed.title)
		expect(wrapper.find('.header').text()).toContain(mockFeed.unreadCount.toString())
	})

	it('should get starred items from state', () => {
		expect((wrapper.findComponent(FeedItemDisplayList)).props().items.length).toEqual(2)
	})

	it('should dispatch FETCH_FEED_ITEMS action if not fetchingItems.starred', () => {
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
