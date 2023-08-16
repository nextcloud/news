import Vuex, { Store } from 'vuex'
import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import Starred from '../../../../src/components/Starred.vue'
import VirtualScroll from '../../../../src/components/VirtualScroll.vue'
import FeedItem from '../../../../src/components/FeedItem.vue'

jest.mock('@nextcloud/axios')

describe('Explore.vue', () => {
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
					starredLoaded: false,
				},
			},
			actions: {
			},
			getters: {
				starred: () => [mockItem],
			},
		})
		wrapper = shallowMount(Starred, {
			propsData: {
				item: mockItem,
			},
			localVue,
			store,
		})
	})

	it('should initialize with mounted flag set', () => {
		expect(wrapper.vm.$data.mounted).toBeTruthy()
	})

	it('should get starred items from state', () => {
		expect((wrapper.findAllComponents(FeedItem).length)).toEqual(1)
	})

	it('should check starredLoaded and mounted to determine if the virtual scroll has reached end ', () => {
		wrapper.vm.$store.state.items.starredLoaded = false
		expect((wrapper.findComponent(VirtualScroll)).props().reachedEnd).toEqual(false)

		wrapper.vm.$store.state.items.starredLoaded = true
		store.state.items.starredLoaded = true

		wrapper = shallowMount(Starred, {
			propsData: {
				item: mockItem,
			},
			data: () => {
				return {
					mounted: true,
				}
			},
			localVue,
			store,
		})

		expect((wrapper.findComponent(VirtualScroll)).props().reachedEnd).toEqual(true)
	})
})
