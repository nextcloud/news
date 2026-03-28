import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import Vuex from 'vuex'
import FeedInfoTable from '../../../../../src/components/modals/FeedInfoTable.vue'
import { FEED_UPDATE_MODE } from '../../../../../src/enums/index.ts'
import { ACTIONS } from '../../../../../src/store/index.ts'

describe('FeedInfoTable.vue', () => {
	'use strict'

	let wrapper: any
	let store: any

	const feeds = [{
		id: 1,
		title: 'first',
		folderId: 456,
		lastModified: 9,
		nextUpdateTime: 1,
		articlesPerUpdate: 150,
		updateErrorCount: 20,
	}, {
		id: 2,
		title: 'second',
		folderId: 789,
		lastModified: 7,
		nextUpdateTime: 4,
		articlesPerUpdate: 50,
		updateErrorCount: 40,
	}, {
		id: 3,
		title: 'third',
		folderId: 123,
		lastModified: 8,
		nextUpdateTime: 8,
		articlesPerUpdate: 20,
		updateErrorCount: 0,
	}]

	const folders = [{
		id: 123,
		name: 'second',
	}, {
		id: 456,
		name: 'first',
	}, {
		id: 789,
		name: 'third',
	}]

	beforeEach(() => {
		store = new Vuex.Store({
			state: {
				feeds: { feeds },
				folders: { folders },
			},
			getters: {
				feeds: () => [feeds],
				folders: () => [folders],
			},
		})
		store.dispatch = vi.fn()
		wrapper = shallowMount(FeedInfoTable, {
			global: {
				plugins: [store],
			},
		})
	})

	describe('Methods', () => {
		it('should return folder name for a given feed', () => {
			const folderName = wrapper.vm.folderName(feeds[0])
			expect(folderName).toEqual('first')
		})

		it('should set triggers to open and close move feed dialog', () => {
			expect(wrapper.vm.feedToMove).toEqual(undefined)
			expect(wrapper.vm.showMoveFeed).toEqual(false)

			wrapper.vm.openMoveFeed(feeds[0])
			expect(wrapper.vm.feedToMove).toEqual(feeds[0])
			expect(wrapper.vm.showMoveFeed).toEqual(true)

			wrapper.vm.closeMoveFeed(feeds[0])
			expect(wrapper.vm.showMoveFeed).toEqual(false)
		})

		it('should set key and ascending order when sort key differs', () => {
			wrapper.vm.sortKey = 'title'
			wrapper.vm.sortOrder = 1

			wrapper.vm.sortBy('id')

			expect(wrapper.vm.sortKey).toEqual('id')
			expect(wrapper.vm.sortOrder).toEqual(1)
		})

		it('should set key and descending order when sort key is equal', () => {
			wrapper.vm.sortKey = 'id'
			wrapper.vm.sortOrder = 1

			wrapper.vm.sortBy('id')

			expect(wrapper.vm.sortKey).toEqual('id')
			expect(wrapper.vm.sortOrder).toEqual(-1)
		})
	})

	describe('Table Sorting', () => {
		it('sort the feed table in ascending order by id', () => {
			wrapper.vm.sortKey = 'id'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])
		})

		it('sort the feed table in descending order by id', () => {
			wrapper.vm.sortKey = 'id'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])
		})

		it('sort the feed table in ascending order by title', () => {
			wrapper.vm.sortKey = 'title'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])
		})

		it('sort the feed table in descending order by title', () => {
			wrapper.vm.sortKey = 'title'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])
		})

		it('sort the feed table in ascending order by folderId', () => {
			wrapper.vm.sortKey = 'folderId'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[0], feeds[1]])
		})

		it('sort the feed table in descending order by folderId', () => {
			wrapper.vm.sortKey = 'folderId'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[1], feeds[0], feeds[2]])
		})

		it('sort the feed table in ascending order by lastModified', () => {
			wrapper.vm.sortKey = 'lastModified'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[1], feeds[2], feeds[0]])
		})

		it('sort the feed table in descending order by lastModified', () => {
			wrapper.vm.sortKey = 'lastModified'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[0], feeds[2], feeds[1]])
		})

		it('sort the feed table in ascending order by nextUpdateTime', () => {
			wrapper.vm.sortKey = 'nextUpdateTime'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])
		})

		it('sort the feed table in descending order by nextUpdateTime', () => {
			wrapper.vm.sortKey = 'nextUpdateTime'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])
		})

		it('sort the feed table in ascending order by articlesPerUpdate', () => {
			wrapper.vm.sortKey = 'articlesPerUpdate'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])
		})

		it('sort the feed table in descending order by articlesPerUpdate', () => {
			wrapper.vm.sortKey = 'articlesPerUpdate'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])
		})

		it('sort the feed table in ascending order by updateErrorCount', () => {
			wrapper.vm.sortKey = 'updateErrorCount'
			wrapper.vm.sortOrder = 1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[2], feeds[0], feeds[1]])
		})

		it('sort the feed table in descending order by updateErrorCount', () => {
			wrapper.vm.sortKey = 'updateErrorCount'
			wrapper.vm.sortOrder = -1

			const sortedFeeds = wrapper.vm.$options.computed?.sortedFeeds.call(wrapper.vm)
			expect(sortedFeeds).toEqual([feeds[1], feeds[0], feeds[2]])
		})
	})

	describe('Feed Actions', () => {
		it('should dispatch message to store with feed object and preventUpdate', () => {
			wrapper.vm.setPreventUpdate(feeds[0], true)

			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed: feeds[0], preventUpdate: true })
		})

		it('should dispatch message to store with feed object and new updateMode', () => {
			wrapper.vm.setUpdateMode(feeds[0], FEED_UPDATE_MODE.IGNORE)

			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, { feed: feeds[0], updateMode: FEED_UPDATE_MODE.IGNORE })
		})

		it('should dispatch message to store with feed object and fullTextEnabled', () => {
			wrapper.vm.setFullText(feeds[0], true)

			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, { feed: feeds[0], fullTextEnabled: true })
		})
	})

	afterEach(() => {
		vi.clearAllMocks()
	})
})
