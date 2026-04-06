import { mount, shallowMount } from '@vue/test-utils'
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
		updateMode: FEED_UPDATE_MODE.NORMAL,
		fullTextEnabled: false,
		preventUpdate: true,
	}, {
		id: 2,
		title: 'second',
		folderId: 789,
		lastModified: 7,
		nextUpdateTime: 4,
		articlesPerUpdate: 50,
		updateErrorCount: 40,
		updateMode: FEED_UPDATE_MODE.SILENT,
		fullTextEnabled: true,
		preventUpdate: false,
	}, {
		id: 3,
		title: 'third',
		folderId: 123,
		lastModified: 8,
		nextUpdateTime: 8,
		articlesPerUpdate: 20,
		updateErrorCount: 0,
		updateMode: FEED_UPDATE_MODE.NORMAL,
		fullTextEnabled: true,
		preventUpdate: true,
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

	describe('Methods', () => {
		beforeEach(() => {
			store = new Vuex.Store({
				state: {
					feeds: { feeds },
					folders: { folders },
				},
				getters: {
					feeds: () => feeds,
					folders: () => folders,
					loading: () => false,
				},
			})
			store.dispatch = vi.fn()
			wrapper = shallowMount(FeedInfoTable, {
				global: {
					plugins: [store],
				},
			})
		})

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
		beforeEach(() => {
			store = new Vuex.Store({
				state: {
					feeds: { feeds },
					folders: { folders },
				},
				getters: {
					feeds: () => feeds,
					folders: () => folders,
					loading: () => false,
				},
			})
			store.dispatch = vi.fn()
			wrapper = shallowMount(FeedInfoTable, {
				global: {
					plugins: [store],
				},
			})
		})

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

	describe('Loading State', () => {
		beforeEach(() => {
			store = new Vuex.Store({
				state: {
					feeds: { feeds },
					folders: { folders },
				},
				getters: {
					feeds: () => feeds,
					folders: () => folders,
					loading: () => true,
				},
			})
			store.dispatch = vi.fn()
			wrapper = mount(FeedInfoTable, {
				global: { plugins: [store] },
				stubs: {
					SidebarFeedLinkActions: true,
				},
			})
		})

		it('should show loading note card when loading is true', async () => {
			const noteCard = wrapper.findAllComponents({ name: 'NcNoteCard' })
				.find((ncnotecard) => ncnotecard.attributes('data-test') === 'loadingMessage')
			expect(noteCard.text()).toContain('Loading feeds')
		})
	})

	describe('Action Buttons', () => {
		beforeEach(() => {
			store = new Vuex.Store({
				state: {
					feeds: { feeds },
					folders: { folders },
				},
				getters: {
					feeds: () => feeds,
					folders: () => folders,
					loading: () => false,
				},
			})
			store.dispatch = vi.fn()
			wrapper = mount(FeedInfoTable, {
				global: { plugins: [store] },
				stubs: {
					SidebarFeedLinkActions: true,
				},
			})
		})

		it('should dispatch setPreventUpdate on click with preventUpdate false', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-1')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'enableFeedUpdate')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed: feeds[0], preventUpdate: false })
		})

		it('should dispatch setPreventUpdate on click with preventUpdate true', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-2')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'disableFeedUpdate')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed: feeds[1], preventUpdate: true })
		})

		it('should dispatch setUpdateMode on click with FEED_UPDATE_MODE.SILENT', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-1')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'disableMarkUnread')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, { feed: feeds[0], updateMode: FEED_UPDATE_MODE.SILENT })
		})

		it('should dispatch setUpdateMode on click with FEED_UPDATE_MODE.NORMAL', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-2')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'enableMarkUnread')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, { feed: feeds[1], updateMode: FEED_UPDATE_MODE.NORMAL })
		})

		it('should dispatch setFullText on click with fullTextEnabled true', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-1')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'enableScraping')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, { feed: feeds[0], fullTextEnabled: true })
		})

		it('should dispatch setFullText on click with fullTextEnabled false', async () => {
			const actions = wrapper.findAllComponents({ name: 'NcActions' })
				.find((ncactions) => ncactions.attributes('data-test') === 'feedOptions-2')

			const button = actions.findAll('button')
				.find((btn) => btn.attributes('data-test') === 'disableScraping')
			expect(button).toBeTruthy()
			await button.trigger('click')

			expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, { feed: feeds[1], fullTextEnabled: false })
		})
	})

	afterEach(() => {
		vi.clearAllMocks()
	})
})
