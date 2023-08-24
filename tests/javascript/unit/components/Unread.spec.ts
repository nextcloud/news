import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import Unread from '../../../../src/components/Unread.vue'
import FeedItemDisplayList from '../../../../src/components/FeedItemDisplayList.vue'

jest.mock('@nextcloud/axios')

describe('Unread.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	localVue.use(Vuex)
	let wrapper: Wrapper<Unread>

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
				},
			},
			actions: {
			},
			getters: {
				unread: () => [mockItem, mockItem],
			},
		})

		store.dispatch = jest.fn()
		store.commit = jest.fn()

		wrapper = shallowMount(Unread, {
			propsData: {
				item: mockItem,
			},
			localVue,
			store,
		})
	})

	it('should get unread items from state', () => {
		expect((wrapper.findComponent(FeedItemDisplayList)).props().items.length).toEqual(2)
	})

	it('should dispatch FETCH_UNREAD action if not fetchingItems.unread', () => {
		(wrapper.vm as any).$store.state.items.fetchingItems.unread = true;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled();

		(wrapper.vm as any).$store.state.items.fetchingItems.unread = false;

		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
