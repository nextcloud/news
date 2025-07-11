import { mount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, ref } from 'vue'
import Vuex, { Store } from 'vuex'
import ContentTemplate from '../../../../src/components/ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../../../src/store/index.ts'

vi.mock('@nextcloud/axios')

describe('ContentTemplate.vue', () => {
	'use strict'

	let oldestFirst = false
	let selectedId = ref(null)
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
	}

	const commitStub = vi.fn((mutation, param) => {
		if (mutation === MUTATIONS.SET_SELECTED_ITEM) {
			selectedId.value = param.id
		}
	})

	const dispatchStub = vi.fn((action, param) => {
		if (action === ACTIONS.MARK_READ) {
			param.item.unread = false
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
					},
					lastItemLoaded: {
						unread: 4,
					},
					fetchingItems: {
						unread: false,
					},
					allItems: [mockItem1, mockItem2, mockItem3, mockItem4],
				},
				feeds: {
				},
			},
			getters: {
				feeds: () => [mockFeed],
				selected: () => store.state.items.allItems.find((item: FeedItem) => item.id === selectedId.value),
			},
		})
		store.commit = commitStub
		store.dispatch = dispatchStub
	})

	beforeEach(() => {
		wrapper = mount(ContentTemplate, {
			attachTo: document.body,
			props: {
				items: [mockItem1, mockItem2, mockItem3, mockItem4],
				fetchKey: 'unread',
			},
			global: {
				plugins: [store],
			},
		})
                vi.clearAllMocks()
	})

	it('should set selected item id and call mark read if unread', async () => {
		expect(wrapper.vm.selectedFeedItem).toEqual(undefined)
		wrapper.vm.selectItem(mockItem1)
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id, key: 'unread' })
		expect(store.dispatch).toBeCalledWith(ACTIONS.MARK_READ, { item: mockItem1 })
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem1)
	})

	it('should set selected item id and do not call mark read if read', async () => {
		mockItem1.unread = false
		expect(wrapper.vm.selectedFeedItem).toEqual(undefined)
		wrapper.vm.selectItem(mockItem1)
		expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id, key: 'unread' })
		expect(store.dispatch).not.toHaveBeenCalled()
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem1)
	})

	it('should select previous item', () => {
		wrapper.vm.selectItem(mockItem2)
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem2)
		wrapper.vm.previousItem()
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem1)
	})

	it('should select next item', () => {
		wrapper.vm.selectItem(mockItem2)
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem2)
		wrapper.vm.nextItem()
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem3)
	})

	it('should select first item', () => {
		expect(wrapper.vm.selectedFeedItem).toEqual(undefined)
		wrapper.vm.nextItem()
		expect(wrapper.vm.selectedFeedItem).toEqual(mockItem1)
	})

})
