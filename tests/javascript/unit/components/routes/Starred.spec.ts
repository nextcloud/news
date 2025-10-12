import { nextTick } from 'vue'
import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Starred from '../../../../../src/components/routes/Starred.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

describe('Starred.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 1,
			feedId: 1,
			title: 'feed item',
			pubDate: Date.now() / 1000,
			unread: true,
			starred: true,
		}, {
			id: 2,
			feedId: 1,
			title: 'feed item 2',
			pubDate: Date.now() / 1000,
			unread: true,
			starred: true,
		}, {
			id: 3,
			feedId: 1,
			title: 'feed item 3',
			pubDate: Date.now() / 1000,
			starred: true,
		}, {
			id: 4,
			feedId: 1,
			title: 'feed item 4',
			pubDate: Date.now() / 1000,
			starred: true,
		}
	]

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						starred: false,
					},
					lastItemLoaded: {
						starred: 1,
					},
				},
				feeds: {
				},
				app: {
					oldestFirst: false,
				},
			},
			actions: {
			},
			getters: {
				starred: () => mockItems,
				oldestFirst: (state) => state.app.oldestFirst,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Starred, {
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get starred items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(4)
	})

	it('should get only first item from state ordering oldest>newest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.starred = 1;
		(wrapper.vm as any).$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state ordering newest>oldest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded.starred = 4;
		(wrapper.vm as any).$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_STARRED action if not fetchingItems.starred', () => {
                (wrapper.vm as any).$store.state.items.fetchingItems.starred = false;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_STARRED action if fetchingItems.starred', () => {
                (wrapper.vm as any).$store.state.items.fetchingItems.starred = true;
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})
})
