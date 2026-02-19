import type { Store } from 'vuex'

import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, ref } from 'vue'
import Vuex from 'vuex'
import ContentTemplate from '../../../../src/components/ContentTemplate.vue'
import { DISPLAY_MODE, SPLIT_MODE } from '../../../../src/enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../../../src/store/index.ts'

vi.mock('@nextcloud/browser-storage', () => {
	const builder = {
		persist: vi.fn().mockReturnThis(),
		build: vi.fn(() => ({
			getItem: vi.fn(() => 'true'),
			setItem: vi.fn(),
			removeItem: vi.fn(),
		})),
	}

	return {
		getBuilder: vi.fn(() => builder),
	}
})

vi.mock('@nextcloud/vue/composables/useIsMobile', () => ({
	useIsMobile: () => false,
}))

describe('ContentTemplate.vue', () => {
	'use strict'

	const selectedId = ref(null)
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
		HTMLElement.prototype.scrollTo = vi.fn()
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
				app: {
					displaymode: DISPLAY_MODE.DEFAULT,
					splitmode: SPLIT_MODE.VERTICAL,
				},
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
				splitmode: (state) => state.app.splitmode,
				displaymode: (state) => state.app.displaymode,
			},
		})
		store.commit = commitStub
		store.dispatch = dispatchStub
	})

	describe('item selection methods', () => {
		beforeEach(() => {
			wrapper = shallowMount(ContentTemplate, {
				props: {
					items: [mockItem1, mockItem2, mockItem3, mockItem4],
					fetchKey: 'unread',
				},
				global: {
					plugins: [store],
				},
			})
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

		afterEach(() => {
			wrapper?.unmount()
			vi.clearAllMocks()
		})
	})

	describe('initial item auto-selection', () => {
		beforeEach(() => {
			vi.clearAllMocks()
		})

		it('should auto select first item with SPLIT_MODE.OFF and showDetails is set', async () => {
			store.state.app.splitmode = SPLIT_MODE.OFF
			store.state.items.fetchingItems.item = false
			wrapper = shallowMount(ContentTemplate, {
				props: {
					items: [mockItem1, mockItem2, mockItem3, mockItem4],
					fetchKey: 'unread',
				},
				global: {
					plugins: [store],
				},
			})
			wrapper.vm.showDetails = true
			wrapper.vm.initialSelection = true

			await nextTick()

			expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id, key: 'unread' })
			expect(wrapper.vm.initialSelection).toBe(false)
		})

		it('should not auto select when fetchingItems is true', async () => {
			store.state.app.splitmode = SPLIT_MODE.OFF
			store.state.items.fetchingItems.unread = true
			wrapper = shallowMount(ContentTemplate, {
				props: {
					items: [mockItem1, mockItem2, mockItem3, mockItem4],
					fetchKey: 'unread',
				},
				global: {
					plugins: [store],
				},
			})

			wrapper.vm.showDetails = true
			wrapper.vm.initialSelection = true

			await nextTick()

			expect(store.commit).not.toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id, key: 'unread' })
			expect(wrapper.vm.initialSelection).toBe(true)
		})

		it('should select first item when fetchKey is item', async () => {
			store.state.app.splitmode = SPLIT_MODE.VERTICAL
			store.state.items.fetchingItems.item = false
			wrapper = shallowMount(ContentTemplate, {
				props: {
					items: [mockItem1],
					fetchKey: 'item',
				},
				global: {
					plugins: [store],
				},
			})

			wrapper.vm.showDetails = false
			wrapper.vm.initialSelection = true
			store.state.items.fetchingItems.item = false

			await wrapper.vm.$nextTick()

			expect(store.commit).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem1.id, key: 'item' })
		})

		afterEach(() => {
			wrapper?.unmount()
			vi.clearAllMocks()
		})
	})

	describe('displayMode watcher', () => {
		beforeEach(() => {
			wrapper = shallowMount(ContentTemplate, {
				props: {
					items: [mockItem1, mockItem2, mockItem3, mockItem4],
					fetchKey: 'unread',
				},
				global: {
					plugins: [store],
				},
			})
		})

		it('should enable page hotkeys when display mode is SCREENREADER', async () => {
			store.state.app.displaymode = DISPLAY_MODE.SCREENREADER

			await nextTick()

			expect(wrapper.vm.stopPageUpHotkey).not.toBe(null)
			expect(wrapper.vm.stopPageDownHotkey).not.toBe(null)
		})

		it('should disable page hotkeys when display mode is not SCREENREADER', async () => {
			store.state.app.displaymode = DISPLAY_MODE.DEFAULT
			await nextTick()

			expect(wrapper.vm.stopPageUpHotkey).toBe(null)
			expect(wrapper.vm.stopPageDownHotkey).toBe(null)
		})

		it('should set showDetails to false when switching display mode', async () => {
			wrapper.vm.showDetails = true
			store.state.app.displaymode = DISPLAY_MODE.COMPACT
			await nextTick()

			expect(wrapper.vm.showDetails).toBe(false)
		})

		afterEach(() => {
			wrapper?.unmount()
			vi.clearAllMocks()
		})
	})
})
