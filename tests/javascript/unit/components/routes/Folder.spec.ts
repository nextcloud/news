import type { Store } from 'vuex'

import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import Vuex from 'vuex'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'
import Folder from '../../../../../src/components/routes/Folder.vue'

describe('Folder.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 1,
			feedId: 789,
			title: 'feed item',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 2,
			feedId: 456,
			title: 'feed item 2',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 3,
			feedId: 456,
			title: 'feed item 3',
			pubDate: Date.now() / 1000,
			unread: true,
		}, {
			id: 4,
			feedId: 789,
			title: 'feed item 4',
			pubDate: Date.now() / 1000,
			unread: true,
		},
	]

	const mockFeed = {
		id: 789,
		title: 'feed name',
		unreadCount: 2,
		folderId: 123,
	}

	const mockFeed2 = {
		id: 456,
		title: 'feed name 2',
		unreadCount: 2,
		folderId: 123,
	}

	const mockFolder = {
		id: 123,
		name: 'foldername',
	}

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						'folder-123': false,
					},
					lastItemLoaded: {
						'folder-123': 1,
					},
					allItems: mockItems,
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
				feeds: () => [mockFeed, mockFeed2],
				folders: () => [mockFolder],
				oldestFirst: (state) => state.app.oldestFirst,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Folder, {
			props: {
				folderId: '123',
			},
			global: {
				mocks: {
					$route: {
						params: {},
					},
				},
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get folder items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(4)
	})

	it('should get only first item from state ordering oldest>newest', async () => {
		wrapper.vm.$store.state.items.lastItemLoaded['folder-123'] = 1
		wrapper.vm.$store.state.app.oldestFirst = true
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should get only first item from state ordering newest>oldest', async () => {
		wrapper.vm.$store.state.items.lastItemLoaded['folder-123'] = 4
		wrapper.vm.$store.state.app.oldestFirst = false
		await nextTick
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(1)
	})

	it('should dispatch FETCH_FOLDER_FEED_ITEMS action on fetchMore if not fetchingItems.folder-123', () => {
		wrapper.vm.$store.state.items.fetchingItems['folder-123'] = false
		wrapper.vm.fetchMore()
		expect(store.dispatch).toBeCalled()
	})

	it('should not dispatch FETCH_FOLDER_FEED_ITEMS action on fetchMore if fetchingItems.folder-123', () => {
		wrapper.vm.$store.state.items.fetchingItems['folder-123'] = true
		wrapper.vm.fetchMore()
		expect(store.dispatch).not.toBeCalled()
	})

	it('should dispatch FEED_MARK_READ action on markRead', () => {
		wrapper.vm.markRead()
		expect(store.dispatch).toBeCalledTimes(2)
	})

	it('should return folder unread count', () => {
		expect(wrapper.vm.unreadCount).toEqual(4)
	})
})
