import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import All from '../../../../../src/components/routes/All.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

jest.mock('@nextcloud/axios')

describe('All.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	localVue.use(Vuex)
	let wrapper: Wrapper<All>

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

		store.dispatch = jest.fn()
		store.commit = jest.fn()

		wrapper = shallowMount(All, {
			propsData: {
				item: mockItem,
			},
			localVue,
			store,
		})
	})

	it('should get all items from state', () => {
		console.warn('PROPS', wrapper.findComponent(ContentTemplate).props());
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
