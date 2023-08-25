import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import FeedItemDisplayList from '../../../../src/components/FeedItemDisplayList.vue'
import VirtualScroll from '../../../../src/components/VirtualScroll.vue'
import FeedItemRow from '../../../../src/components/FeedItemRow.vue'

jest.mock('@nextcloud/axios')

describe('FeedItemDisplayList.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	localVue.use(Vuex)
	let wrapper: Wrapper<FeedItemDisplayList>

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
					allItemsLoaded: {
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

		wrapper = shallowMount(FeedItemDisplayList, {
			propsData: {
				items: [mockItem],
				fetchKey: 'unread',
			},
			localVue,
			store,
		})
	})

	it('should create FeedItemRow items from input', () => {
		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(1)

		wrapper = shallowMount(FeedItemDisplayList, {
			propsData: {
				items: [mockItem, mockItem],
				fetchKey: 'unread',
			},
			localVue,
			store,
		})
		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(2)
	})

})
