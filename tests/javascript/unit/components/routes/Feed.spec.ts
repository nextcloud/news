import type { Store } from 'vuex'

import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import Vuex from 'vuex'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'
import Feed from '../../../../../src/components/routes/Feed.vue'
import { FEED_ORDER } from '../../../../../src/enums/index.ts'
import { ACTIONS } from '../../../../../src/store'

describe('Feed.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 1,
			feedId: 123,
			title: 'feed item',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 2,
			feedId: 123,
			title: 'feed item 2',
			pubDate: Date.now() / 1000,
		}, {
			id: 3,
			feedId: 123,
			title: 'feed item 3',
			pubDate: Date.now() / 1000,
		}, {
			id: 4,
			feedId: 123,
			title: 'feed item 4',
			pubDate: Date.now() / 1000,
			unread: true,
		},
	]

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
					allItemsLoaded: {
						'feed-123': false,
					},
					lastItemLoaded: {
						'feed-123': 1,
					},
					fetchingItems: {
						'feed-123': false,
					},
					allItems: mockItems,
				},
				feeds: {
					ordering: {
						'feed-123': FEED_ORDER.DEFAULT,
					},
				},
				app: {
					loading: false,
					showAll: false,
					oldestFirst: false,
				},
			},
			actions: {
			},
			mutations: {
				SET_SHOW_ALL(state, value) {
					state.app.showAll = value
					state.items.newestItemId = 0
				},
				SET_LAST_ITEM_LOADED(state, { key, lastItem }) {
					state.items.lastItemLoaded[key] = lastItem
				},
			},
			getters: {
				feeds: () => [mockFeed],
				showAll: (state) => state.app.showAll,
				loading: (state) => state.app.loading,
				oldestFirst: (state) => state.app.oldestFirst,
			},
		})

		store.dispatch = vi.fn()
	})

	beforeEach(() => {
		vi.clearAllMocks()

		wrapper = shallowMount(Feed, {
			props: {
				feedId: '123',
			},
			global: {
				plugins: [store],
			},
		})
		wrapper.vm.$store.state.items.lastItemLoaded['feed-123'] = 1
		wrapper.vm.$store.state.items.newestItemId = 4
	})

	it('should get two feed items from state when showAll is disabled', async () => {
		store.commit('SET_SHOW_ALL', false)
		await nextTick()
		expect(wrapper.vm.$store.getters.showAll).toEqual(false)
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
	})

	it('should get four feed items from state when showAll is enabled', async () => {
		store.commit('SET_SHOW_ALL', true)
		await nextTick()
		expect(wrapper.vm.$store.getters.showAll).toEqual(true)
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(4)
	})

	it('should clear unread cache when changing feed', async () => {
		store.commit('SET_SHOW_ALL', false)
		await nextTick()
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
		await wrapper.setProps({
			feedId: '124',
		})
		expect(wrapper.vm.$store.getters.showAll).toEqual(false)
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(0)
	})

	it('should get only first item from state with ordering oldest>newest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded['feed-123'] = 1;
		(wrapper.vm as any).$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state with ordering newest>oldest', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded['feed-123'] = 4;
		(wrapper.vm as any).$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state with FEED_ORDER.OLDEST', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded['feed-123'] = 1;
		(wrapper.vm as any).$store.state.feeds.ordering['feed-123'] = FEED_ORDER.OLDEST;
		(wrapper.vm as any).$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state with FEED_ORDER.NEWEST', async () => {
		(wrapper.vm as any).$store.state.items.lastItemLoaded['feed-123'] = 4;
		(wrapper.vm as any).$store.state.feeds.ordering['feed-123'] = FEED_ORDER.NEWEST;
		(wrapper.vm as any).$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_FEED_ITEMS action if not fetchingItems.feed-123', () => {
		wrapper.vm.$store.state.items.fetchingItems['feed-123'] = false
		wrapper.vm.fetchMore()
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEED_ITEMS, { feedId: 123 })
	})

	it('should not dispatch FETCH_FEED_ITEMS action if fetchingItems.feed-123', () => {
		wrapper.vm.$store.state.items.fetchingItems['feed-123'] = true
		wrapper.vm.fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})

	it('should dispatch FETCH_FEED_ITEMS action from content template emit', async () => {
		wrapper.vm.$store.state.items.fetchingItems['feed-123'] = false
		wrapper.findComponent(ContentTemplate).vm.$emit('load-more')
		await nextTick()
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEED_ITEMS, { feedId: 123 })
	})

	it('should dispatch FEED_MARK_READ action', () => {
		wrapper.vm.markRead()
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_MARK_READ, { feed: mockFeed })
	})

	it('should dispatch FEED_MARK_READ from content template emit', () => {
		wrapper.findComponent(ContentTemplate).vm.$emit('mark-read')
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_MARK_READ, { feed: mockFeed })
	})

	it('should hide content template and not dispatch FETCH_FEED_ITEMS during initial loading', async () => {
		wrapper.vm.$store.state.app.loading = true
		await nextTick()
		wrapper.vm.fetchMore()
		expect(store.dispatch).not.toBeCalled()
		expect(wrapper.vm.$store.getters.loading).toEqual(true)
		expect(wrapper.findComponent(ContentTemplate).exists()).toBe(false)
	})
})
