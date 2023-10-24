import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import Starred from '../../../../../src/components/routes/Starred.vue'
import FeedItemDisplayList from '../../../../../src/components/feed-display/FeedItemDisplayList.vue'

jest.mock('@nextcloud/axios')

describe('Starred.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	localVue.use(Vuex)
	let wrapper: Wrapper<Starred>

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

		store.dispatch = jest.fn()
		store.commit = jest.fn()

		wrapper = shallowMount(Starred, {
			propsData: {
				item: mockItem,
			},
			localVue,
			store,
		})
	})

	it('should get starred items from state', () => {
		expect((wrapper.findComponent(FeedItemDisplayList)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_STARRED action if not fetchingItems.starred', () => {
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
