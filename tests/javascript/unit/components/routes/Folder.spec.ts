import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, describe, expect, it, vi } from 'vitest'

import Folder from '../../../../../src/components/routes/Folder.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

vi.mock('@nextcloud/axios')

describe('Folder.vue', () => {
	'use strict'
	let wrapper: any

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

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					fetchingItems: {
						'folder-123': false,
					},
					allItems: [{
						feedId: 789,
						title: 'feed item',
					}, {
						feedId: 456,
						title: 'feed item 2',
					}],
				},
			},
			actions: {
			},
			getters: {
				feeds: () => [mockFeed, mockFeed2],
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

	it('should get folder items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
	})

	it('should dispatch FETCH_FOLDER_FEED_ITEMS action on fetchMore', () => {
		(wrapper.vm as any).fetchMore()
		expect(store.dispatch).toBeCalled()
	})
})
