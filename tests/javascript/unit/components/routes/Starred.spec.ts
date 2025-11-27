import type { Store } from 'vuex'

import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import Vuex from 'vuex'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'
import Starred from '../../../../../src/components/routes/Starred.vue'
import { ACTIONS } from '../../../../../src/store/index.ts'

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
		},
	]

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						'starred-0': false,
						starred: false,
					},
					lastItemLoaded: {
						starred: 1,
					},
					starredCount: undefined,
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
				loading: () => false,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		// stub NcCounterBubble and ContentTemplate so counter and props are queryable
		wrapper = shallowMount(Starred, {
			global: {
				plugins: [store],
				stubs: {
					NcCounterBubble: {
						props: ['count'],
						template: '<span class="nc-counter" :data-count="count">{{ count }}</span>',
					},
					// declare props so wrapper.findComponent(ContentTemplate).props() contains items/fetchKey
					ContentTemplate: {
						props: ['items', 'fetchKey'],
						template: '<div><slot name="header"></slot><slot /></div>',
					},
				},
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
		wrapper.vm.$store.state.items.lastItemLoaded.starred = 1
		wrapper.vm.$store.state.app.oldestFirst = true
		await nextTick()
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state ordering newest>oldest', async () => {
		wrapper.vm.$store.state.items.lastItemLoaded.starred = 4
		wrapper.vm.$store.state.app.oldestFirst = false
		await nextTick()
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_STARRED action if not fetchingItems.starred', () => {
		wrapper.vm.$store.state.items.fetchingItems.starred = false
		wrapper.vm.fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_STARRED action if fetchingItems.starred', () => {
		wrapper.vm.$store.state.items.fetchingItems.starred = true
		wrapper.vm.fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})

	it('should display the header counter using items.starredCount when no feedId is provided', async () => {
		// set a starredCount on state items
		wrapper.vm.$store.state.items.starredCount = 99
		await nextTick()

		// find stubbed counter and assert attribute equals starredCount
		const counter = wrapper.find('.nc-counter')
		expect(counter.exists()).toBe(true)
		expect(counter.attributes('data-count')).toBe('99')
	})

	it('should display the header counter only counting starred items for current feed, fetchKey includes id, and fetchMore dispatches with feed id', async () => {
		// prepare a store with mixed feed ids
		const mixedItems = [
			{ id: 10, feedId: 2, starred: true },
			{ id: 11, feedId: 2, starred: true },
			{ id: 12, feedId: 3, starred: true },
		]
		const localStore = new Vuex.Store({
			state: {
				items: { fetchingItems: {}, lastItemLoaded: {}, starredCount: undefined },
				app: { oldestFirst: false },
			},
			getters: {
				starred: () => mixedItems,
				oldestFirst: () => false,
				loading: () => false,
			},
		})
		localStore.dispatch = vi.fn()
		localStore.commit = vi.fn()

		const localWrapper = shallowMount(Starred as any, {
			props: { feedId: 2 },
			global: {
				plugins: [localStore],
				stubs: {
					NcCounterBubble: {
						props: ['count'],
						template: '<span class="nc-counter" :data-count="count">{{ count }}</span>',
					},
					ContentTemplate: {
						props: ['items', 'fetchKey'],
						template: '<div><slot name="header"></slot><slot /></div>',
					},
				},
			},
		})

		await nextTick()

		// fetchKey should include feed id
		expect(localWrapper.vm.fetchKey).toBe('starred-2')

		// NcCounterBubble stub should show count of items with feedId === 2
		const counter = localWrapper.find('.nc-counter')
		expect(counter.exists()).toBe(true)
		expect(counter.attributes('data-count')).toBe('2')

		// fetchMore when not fetching -> dispatch ACTIONS.FETCH_STARRED with numeric feedId
		localWrapper.vm.$store.state.items.fetchingItems['starred-2'] = false
		await (localWrapper.vm as any).fetchMore()
		expect(localStore.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_STARRED, { feedId: 2 })
	})
})
