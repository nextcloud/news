import { mount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import Vuex, { Store } from 'vuex'
import FeedItemDisplayList from '../../../../../src/components/feed-display/FeedItemDisplayList.vue'
import FeedItemRow from '../../../../../src/components/feed-display/FeedItemRow.vue'
import VirtualScroll from '../../../../../src/components/feed-display/VirtualScroll.vue'
import { FEED_ORDER } from '../../../../../src/enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../../../../src/store/index.ts'

vi.mock('@nextcloud/axios')

describe('FeedItemDisplayList.vue', () => {
	'use strict'

	let oldestFirst = false
	let selectedItem = null
	let showAll = false
	let store: Store<any>
	let wrapper: any

	const mockItem1 = {
		id: 1,
		feedId: 1,
		title: 'feed item 1',
		pubDate: Date.now() / 1000,
		unread: true,
		starred: true,
	}

	const mockItem2 = {
		id: 2,
		feedId: 1,
		title: 'feed item 2 ',
		pubDate: Date.now() / 1000,
		unread: true,
		starred: true,
	}

	const mockItem3 = {
		id: 3,
		feedId: 1,
		title: 'feed item 3 ',
		pubDate: Date.now() / 1000,
		unread: true,
	}

	const mockItem4 = {
		id: 4,
		feedId: 1,
		title: 'feed item 4 ',
		pubDate: Date.now() / 1000,
		unread: true,
	}

	const mockFeed = {
		id: 1,
		folderId: 1,
	}

	const mockFolder = {
		id: 1,
	}

	const commitStub = vi.fn((mutation, param) => {
		if (mutation === MUTATIONS.SET_SELECTED_ITEM) {
			selectedItem = param.id
		}
	})

	const dispatchStub = vi.fn((action, param) => {
		if (action === ACTIONS.MARK_READ) {
			param.item.unread = false
		}
		if (action === ACTIONS.MARK_UNREAD) {
			param.item.unread = true
		}
		if (action === ACTIONS.STAR_ITEM) {
			param.item.starred = true
		}
		if (action === ACTIONS.UNSTAR_ITEM) {
			param.item.starred = false
		}
	})

	beforeAll(() => {
		HTMLElement.prototype.scrollIntoView = vi.fn()
		HTMLElement.prototype.getBoundingClientRect = vi.fn(() => ({
			width: 500,
			height: 500,
			top: 0,
			left: 0,
			bottom: 500,
			right: 500,
		}))

		store = new Vuex.Store({
			state: {
				items: {
					allItemsLoaded: {
						unread: false,
						all: false,
						starred: false,
						'feed-1': false,
					},
					lastItemLoaded: {
						unread: 0,
						all: 0,
						starred: 0,
						'feed-1': 0,
					},
					fetchingItems: {
						unread: false,
						all: false,
						starred: false,
						'feed-1': false,
					},
					syncNeeded: false,
				},
				feeds: {
					ordering: {
						'feed-1': FEED_ORDER.DEFAULT,
					},
				},
			},
			getters: {
				feeds: () => [mockFeed],
				folders: () => [mockFolder],
				unread: () => [mockItem1, mockItem2, mockItem3, mockItem4],
				showAll: () => showAll,
				oldestFirst: () => oldestFirst,
				selected: () => selectedItem,
			},
		})
		store.commit = commitStub
		store.dispatch = dispatchStub
	})

	beforeEach(() => {
                vi.clearAllMocks()

		// reset unread status
		mockItem1.unread = true
		mockItem2.unread = true
		mockItem3.unread = true
		mockItem4.unread = true

		wrapper = mount(FeedItemDisplayList, {
			attachTo: document.body,
			props: {
				items: [mockItem1, mockItem2, mockItem3, mockItem4],
				fetchKey: 'unread',
			},
			global: {
				plugins: [store],
				stubs: {
					VirtualScroll: false,
				},
			},
		})
	})

	/*
	 * This test checks whether the correct items are displayed when the route is changed.
	 * It also tests whether items marked as read remain available and only disappear after
	 * the route has been changed.
	 */
	it('should create FeedItemRow items when switching route', async () => {
		await wrapper.setProps({
			items: [mockItem1],
			fetchKey: 'unread',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create one FeedItemRow item from input',
		).toEqual(1)

		// select first item from unread route
		expect(selectedItem).toEqual(undefined)
		await wrapper.vm.clickItem(mockItem1)
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem1 })
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id })
		expect(selectedItem).toEqual(1)
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should select first FeedItemRow item from unread route',
		).toEqual(1)

		// add two unread items
		await wrapper.setProps({
			items: [mockItem1, mockItem2, mockItem3],
			fetchKey: 'unread',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create three FeedItemRow items from input without removing read mockItem1',
		).toEqual(3)

		// add one unread item
		await wrapper.setProps({
			items: [mockItem1, mockItem2, mockItem3, mockItem4],
			fetchKey: 'unread',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create four FeedItemRow items from input without removing read mockItem1',
		).toEqual(4)

		// switch to all route with four items
		await wrapper.setProps({
			items: [mockItem1, mockItem2, mockItem3, mockItem4],
			fetchKey: 'all',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create four FeedItemRow items from input after switching route to all',
		).toEqual(4)

		// select first item from all route
		expect(selectedItem).toEqual(undefined)
		await wrapper.vm.clickItem(mockItem1)
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem1 })
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id })
		expect(selectedItem).toEqual(1)
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should select first FeedItemRow item from all route',
		).toEqual(4)

		// switch to feed 1 route with three unread and one read item
		await wrapper.setProps({
			items: [mockItem2, mockItem3, mockItem4],
			fetchKey: 'feed-1',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create three unread FeedItemRow items from input after switching route to feed 1 without read mockItem1',
		).toEqual(3)

		// select first unread item mockitem2
		expect(selectedItem).toEqual(undefined)
		await wrapper.vm.clickItem(mockItem2)
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem2 })
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem2.id })
		expect(selectedItem).toEqual(2)
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should select first unread FeedItemRow item from feed-1 route',
		).toEqual(3)

		// switch to folder 1 route with two unread
		await wrapper.setProps({
			items: [mockItem3, mockItem4],
			fetchKey: 'folder-1',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create two FeedItemRow items from input after switching route to folder 1 without read mockItem1 and mockItem2',
		).toEqual(2)

		// select first unread item mockitem3
		expect(selectedItem).toEqual(undefined)
		await wrapper.vm.clickItem(mockItem3)
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem3 })
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem3.id })
		expect(selectedItem).toEqual(3)
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should select first unread FeedItemRow item from folder-1 route',
		).toEqual(2)

		// switch to starred route with two starred items
		await wrapper.setProps({
			items: [mockItem1, mockItem2],
			fetchKey: 'starred',
		})
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should create two FeedItemRow items from input after switching route to starred',
		).toEqual(2)

		// select first starred item
		expect(selectedItem).toEqual(undefined)
		await wrapper.vm.clickItem(mockItem1)
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem1 })
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id })
		expect(selectedItem).toEqual(1)
		expect(
			(wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length,
			'should select first FeedItemRow item',
		).toEqual(2)
	})

	it('should create four FeedItemRow items with showAll set', async () => {
		showAll = true
		mockItem1.unread = false
		mockItem2.unread = false
		await wrapper.setProps({
			items: [mockItem1, mockItem2, mockItem3, mockItem4],
			fetchKey: 'unread',
		})
		expect((wrapper.findComponent(VirtualScroll)).findAllComponents(FeedItemRow).length).toEqual(4)
	})

	it('should dispatch STAR_ITEM / UNSTAR_ITEM to toggle starred flag', async () => {
		wrapper.vm.selectedItem = mockItem1
		wrapper.vm.toggleStarred()
		expect(store.dispatch).toBeCalledWith(ACTIONS.UNSTAR_ITEM, { item: mockItem1 })
		wrapper.vm.toggleStarred()
		expect(store.dispatch).toBeCalledWith(ACTIONS.STAR_ITEM, { item: mockItem1 })
	})

	it('should dispatch MARK_READ / MARK_UNREAD to toggle read flag', async () => {
		wrapper.vm.selectedItem = mockItem1
		wrapper.vm.toggleRead()
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem1 })
		wrapper.vm.toggleRead()
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_UNREAD, { item: mockItem1 })
	})

	it('should commit RESET_ITEM_STATES and dispatch FETCH_FEEDS when refreshing app', () => {
		wrapper.vm.refreshApp()
		expect(store.commit).toBeCalledWith(MUTATIONS.RESET_ITEM_STATES)
		expect(store.dispatch).toBeCalledWith(ACTIONS.FETCH_FEEDS)
	})

})
