import { showError } from '@nextcloud/dialogs'
import { flushPromises, mount, shallowMount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import Vuex from 'vuex'
import FeedInfoTable from '../../../../../src/components/modals/FeedInfoTable.vue'
import FeedSettingsDialog from '../../../../../src/components/modals/FeedSettingsDialog.vue'
import { FEED_UPDATE_MODE } from '../../../../../src/enums/index.ts'
import { ACTIONS } from '../../../../../src/store/index.ts'

vi.mock('@nextcloud/dialogs', () => ({
	showError: vi.fn(),
}))

const feeds = [{
	id: 1,
	title: 'first',
	url: 'https://example.test/first.xml',
	location: 'https://example.test/first',
	folderId: 456,
	lastModified: 9,
	nextUpdateTime: 1,
	articlesPerUpdate: 150,
	updateErrorCount: 20,
	lastUpdateError: 'HTTP 503',
	updateMode: FEED_UPDATE_MODE.NORMAL,
	fullTextEnabled: false,
	preventUpdate: true,
	maxUnreadAge: 0,
	hasFilter: true,
	unreadCount: 0,
	starredCount: 0,
	pinned: false,
	ordering: 'default',
}, {
	id: 2,
	title: 'second',
	url: 'https://example.test/second.xml',
	location: 'https://example.test/second',
	folderId: 789,
	lastModified: 7,
	nextUpdateTime: 4,
	articlesPerUpdate: 50,
	updateErrorCount: 40,
	lastUpdateError: 'Timeout',
	updateMode: FEED_UPDATE_MODE.SILENT,
	fullTextEnabled: true,
	preventUpdate: false,
	maxUnreadAge: 7,
	hasFilter: false,
	unreadCount: 0,
	starredCount: 0,
	pinned: false,
	ordering: 'default',
}, {
	id: 3,
	title: 'third',
	url: 'https://example.test/third.xml',
	location: 'https://example.test/third',
	folderId: 123,
	lastModified: 8,
	nextUpdateTime: 8,
	articlesPerUpdate: 20,
	updateErrorCount: 0,
	lastUpdateError: '',
	updateMode: FEED_UPDATE_MODE.NORMAL,
	fullTextEnabled: true,
	preventUpdate: true,
	maxUnreadAge: 2,
	hasFilter: false,
	unreadCount: 0,
	starredCount: 0,
	pinned: false,
	ordering: 'default',
}]

const folders = [
	{ id: 123, name: 'second' },
	{ id: 456, name: 'first' },
	{ id: 789, name: 'third' },
]

function createStore(stateFeeds = feeds, loading = false) {
	const store = new Vuex.Store({
		state: {
			feeds: { feeds: stateFeeds },
			folders: { folders },
		},
		getters: {
			loading: () => loading,
		},
	})
	store.dispatch = vi.fn()
	return store
}

describe('FeedInfoTable.vue', () => {
	let wrapper: any
	let store: ReturnType<typeof createStore>

	beforeEach(() => {
		store = createStore()
		wrapper = shallowMount(FeedInfoTable, {
			global: {
				plugins: [store],
			},
		})
	})

	afterEach(() => {
		vi.restoreAllMocks()
		vi.clearAllMocks()
	})

	it('filters feeds by search, folder, and errors', async () => {
		wrapper.vm.searchQuery = 'example.test/second'
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[1]])

		wrapper.vm.searchQuery = ''
		wrapper.vm.folderFilter = wrapper.vm.folderOptions.find((option: { value: number }) => option.value === 456)
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[0]])

		wrapper.vm.folderFilter = null
		wrapper.vm.errorsOnly = true
		await wrapper.vm.$nextTick()
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[0], feeds[1]])
	})

	it('filters unfiled feeds and falls back to the feed location for URLs', () => {
		const unfiledFeed = { ...feeds[0], folderId: undefined, url: '', location: 'https://fallback.test/feed' }
		const localWrapper = shallowMount(FeedInfoTable, {
			global: {
				plugins: [createStore([unfiledFeed])],
			},
		})

		localWrapper.vm.folderFilter = { value: 0, label: 'No folder' }
		localWrapper.vm.searchQuery = 'fallback.test'

		expect(localWrapper.vm.sortedFeeds).toEqual([unfiledFeed])
		expect(localWrapper.vm.displayFeedUrl({ url: '', location: 'https://fallback.test/feed' })).toBe('fallback.test/feed')
		expect(localWrapper.vm.folderName(unfiledFeed)).toBe('')
	})

	it('selects only currently visible feeds', () => {
		wrapper.vm.searchQuery = 'first'
		wrapper.vm.toggleSelectAllByValue(true)
		expect(wrapper.vm.selectedFeedIds).toEqual(['1'])

		wrapper.vm.toggleSelectAllByValue(false)
		expect(wrapper.vm.selectedFeedIds).toEqual([])
	})

	it('preserves hidden selections when select all is toggled', () => {
		wrapper.vm.selectedFeedIds = ['2']
		wrapper.vm.searchQuery = 'first'
		wrapper.vm.toggleSelectAllByValue(true)
		expect(wrapper.vm.selectedFeedIds).toEqual(['2', '1'])

		wrapper.vm.toggleSelectAllByValue(false)
		expect(wrapper.vm.selectedFeedIds).toEqual(['2'])
	})

	it('sorts filtered feeds in both directions', () => {
		wrapper.vm.sortKey = 'title'
		wrapper.vm.sortOrder = 1
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])

		wrapper.vm.sortBy('title')
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])

		wrapper.vm.sortBy('articlesPerUpdate')
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[2], feeds[1], feeds[0]])

		wrapper.vm.sortBy('articlesPerUpdate')
		expect(wrapper.vm.sortedFeeds).toEqual([feeds[0], feeds[1], feeds[2]])
	})

	it('returns descriptive labels for quick-toggle states', () => {
		expect(wrapper.vm.updateToggleLabel(feeds[0])).toContain('disabled')
		expect(wrapper.vm.updateToggleLabel(feeds[1])).toContain('enabled')
		expect(wrapper.vm.readPolicyLabel(feeds[1])).toContain('Keep read status')
		expect(wrapper.vm.scrapingLabel(feeds[1])).toContain('enabled')
		expect(wrapper.vm.readPolicyLabel(feeds[0])).toContain('Mark updated articles unread')
		expect(wrapper.vm.scrapingLabel(feeds[0])).toContain('disabled')
	})

	it('builds update details for enabled and disabled feeds', () => {
		expect(wrapper.vm.updateDetails(feeds[1])).toContain('Next update:')
		expect(wrapper.vm.updateDetails(feeds[1])).toContain('Articles in last update: 50')
		expect(wrapper.vm.updateDetails(feeds[0])).toContain('Not available')
	})

	it('dispatches quick-toggle updates', async () => {
		await wrapper.vm.setPreventUpdate(feeds[0], false)
		await wrapper.vm.setUpdateMode(feeds[0], FEED_UPDATE_MODE.SILENT)
		await wrapper.vm.setFullText(feeds[0], true)

		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, {
			feed: feeds[0],
			preventUpdate: false,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, {
			feed: feeds[0],
			updateMode: FEED_UPDATE_MODE.SILENT,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, {
			feed: feeds[0],
			fullTextEnabled: true,
		})
	})

	it('opens the feed configuration', async () => {
		wrapper.vm.openFeedConfiguration(feeds[0])
		await wrapper.vm.$nextTick()
		expect(wrapper.vm.configuredFeed).toEqual(feeds[0])
		expect(wrapper.findComponent({ name: 'NcModal' }).exists()).toBe(false)
	})

	it('opens bulk move with the selected feeds', () => {
		wrapper.vm.selectedFeedIds = ['1', '2']
		wrapper.vm.openMoveSelectedFeedsDialog()
		expect(wrapper.vm.feedsToMove).toEqual([feeds[0], feeds[1]])
		expect(wrapper.vm.showMoveFeed).toBe(true)
	})

	it('opens a single-feed move and closes it cleanly', () => {
		wrapper.vm.configuredFeed = feeds[0]
		wrapper.vm.openMoveFeed(feeds[0])

		expect(wrapper.vm.configuredFeed).toBeUndefined()
		expect(wrapper.vm.feedsToMove).toEqual([feeds[0]])

		wrapper.vm.closeMoveFeed()
		expect(wrapper.vm.feedsToMove).toEqual([])
		expect(wrapper.vm.showMoveFeed).toBe(false)
	})

	it('does not open bulk move without a selection', () => {
		wrapper.vm.openMoveSelectedFeedsDialog()
		expect(wrapper.vm.showMoveFeed).toBe(false)
	})

	it('updates selected feeds sequentially', async () => {
		store.dispatch = vi.fn().mockResolvedValue(undefined)
		vi.spyOn(wrapper.vm, 'pauseBetweenBatchRequests').mockResolvedValue(undefined)
		wrapper.vm.selectedFeedIds = ['1', '2']

		await wrapper.vm.bulkSetPreventUpdate(false)

		expect(store.dispatch).toHaveBeenNthCalledWith(1, ACTIONS.FEED_SET_PREVENT_UPDATE, {
			feed: feeds[0],
			preventUpdate: false,
		})
		expect(store.dispatch).toHaveBeenNthCalledWith(2, ACTIONS.FEED_SET_PREVENT_UPDATE, {
			feed: feeds[1],
			preventUpdate: false,
		})
		expect(wrapper.vm.selectedFeedIds).toEqual([])
	})

	it('retains failed selections after a partial bulk update', async () => {
		vi.spyOn(console, 'error').mockImplementation(() => {})
		vi.spyOn(wrapper.vm, 'pauseBetweenBatchRequests').mockResolvedValue(undefined)
		store.dispatch = vi.fn()
			.mockResolvedValueOnce(undefined)
			.mockRejectedValueOnce(new Error('network'))
		wrapper.vm.selectedFeedIds = ['1', '2']

		await wrapper.vm.bulkSetUpdateMode(FEED_UPDATE_MODE.SILENT)

		expect(wrapper.vm.selectedFeedIds).toEqual(['2'])
		expect(showError).toHaveBeenCalled()
	})

	it('does not run batch actions while processing or without a selection', async () => {
		const action = vi.fn()
		wrapper.vm.processingBatch = true
		wrapper.vm.selectedFeedIds = ['1']

		await wrapper.vm.runBatch(action, 'failed')
		expect(action).not.toHaveBeenCalled()

		wrapper.vm.processingBatch = false
		wrapper.vm.selectedFeedIds = []
		await wrapper.vm.runBatch(action, 'failed')
		expect(action).not.toHaveBeenCalled()
	})

	it('deletes selected feeds after confirmation', async () => {
		vi.spyOn(window, 'confirm').mockReturnValue(true)
		vi.spyOn(wrapper.vm, 'pauseBetweenBatchRequests').mockResolvedValue(undefined)
		store.dispatch = vi.fn().mockResolvedValue(undefined)
		wrapper.vm.selectedFeedIds = ['1', '2']

		await wrapper.vm.deleteSelectedFeeds()

		expect(store.dispatch).toHaveBeenNthCalledWith(1, ACTIONS.FEED_DELETE, { feed: feeds[0] })
		expect(store.dispatch).toHaveBeenNthCalledWith(2, ACTIONS.FEED_DELETE, { feed: feeds[1] })
	})

	it('does not delete when nothing is selected or confirmation is declined', async () => {
		store.dispatch = vi.fn()

		await wrapper.vm.deleteSelectedFeeds()
		expect(store.dispatch).not.toHaveBeenCalled()

		wrapper.vm.selectedFeedIds = ['1']
		vi.spyOn(window, 'confirm').mockReturnValue(false)
		await wrapper.vm.deleteSelectedFeeds()
		expect(store.dispatch).not.toHaveBeenCalled()
	})

	it('resets filters and removes stale selected feed IDs', () => {
		wrapper.vm.searchQuery = 'query'
		wrapper.vm.folderFilter = { value: 123, label: 'folder' }
		wrapper.vm.errorsOnly = true
		wrapper.vm.selectedFeedIds = ['1', 'missing']

		wrapper.vm.resetFilters()
		expect(wrapper.vm.searchQuery).toBe('')
		expect(wrapper.vm.folderFilter).toBeNull()
		expect(wrapper.vm.errorsOnly).toBe(false)

		wrapper.vm.$options.watch.feeds.handler.call(wrapper.vm)
		expect(wrapper.vm.selectedFeedIds).toEqual(['1'])
	})

	it('tracks all, partial, and empty selection states', () => {
		expect(wrapper.vm.allSelected).toBe(false)
		expect(wrapper.vm.isPartiallySelected).toBe(false)

		wrapper.vm.selectedFeedIds = ['1']
		expect(wrapper.vm.isFeedSelected(feeds[0])).toBe(true)
		expect(wrapper.vm.isPartiallySelected).toBe(true)

		wrapper.vm.selectedFeedIds = ['1', '2', '3']
		expect(wrapper.vm.allSelected).toBe(true)
		expect(wrapper.vm.isPartiallySelected).toBe(false)
	})

	it('renders no legend and shows the errors badge', () => {
		const fullWrapper = mount(FeedInfoTable, {
			global: {
				plugins: [createStore()],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
					},
				},
			},
		})

		expect(fullWrapper.text()).not.toContain('Feed option icons')
		expect(fullWrapper.findAll('.error-badge')).toHaveLength(2)
		const errorsFilter = fullWrapper.get('input[aria-label="Filter feeds with errors"]')
		expect(fullWrapper.text()).toContain('Feeds with errors')
		expect(errorsFilter.attributes('disabled')).toBeUndefined()
		expect(fullWrapper.findAllComponents({ name: 'NcCounterBubble' })).toHaveLength(1)
	})

	it('disables the errors filter and hides its count when there are no errors', () => {
		const healthyFeeds = [{ ...feeds[0], updateErrorCount: 0 }]
		const healthyWrapper = mount(FeedInfoTable, {
			global: {
				plugins: [createStore(healthyFeeds)],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
					},
				},
			},
		})
		const errorsFilter = healthyWrapper.get('input[aria-label="Filter feeds with errors"]')

		expect(errorsFilter.attributes('disabled')).toBeDefined()
		expect(healthyWrapper.findAllComponents({ name: 'NcCounterBubble' })).toHaveLength(0)
	})

	it('renders quick-toggle actions and forwards modal close', async () => {
		const actionStore = createStore()
		const fullWrapper = mount(FeedInfoTable, {
			global: {
				plugins: [actionStore],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
						name: 'NcModal',
					},
				},
			},
		})
		const updateButton = fullWrapper.get('button[aria-label="Feed updates disabled — click to enable"]')

		await updateButton.trigger('click')
		expect(actionStore.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, {
			feed: feeds[0],
			preventUpdate: false,
		})

		fullWrapper.findComponent({ name: 'NcModal' }).vm.$emit('close')
		expect(fullWrapper.emitted('close')).toBeTruthy()
	})

	it('renders loading and empty-filter states', async () => {
		const loadingStore = createStore(feeds, true)
		const loadingWrapper = mount(FeedInfoTable, {
			global: {
				plugins: [loadingStore],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
					},
				},
			},
		})
		expect(loadingWrapper.text()).toContain('Loading feeds')

		wrapper.vm.searchQuery = 'no matching feed'
		await wrapper.vm.$nextTick()
		expect(wrapper.vm.sortedFeeds).toEqual([])
	})
})

describe('FeedSettingsDialog.vue', () => {
	let wrapper: any
	let store: ReturnType<typeof createStore>

	beforeEach(() => {
		store = createStore()
		store.dispatch = vi.fn().mockResolvedValue({})
		wrapper = shallowMount(FeedSettingsDialog, {
			props: {
				feed: feeds[0],
			},
			global: {
				plugins: [store],
			},
		})
	})

	afterEach(() => {
		vi.restoreAllMocks()
	})

	it('loads and saves keyword filters', async () => {
		await wrapper.vm.saveFilter()
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SAVE_FILTER, {
			feed: feeds[0],
			titleKeywords: '',
			bodyKeywords: '',
			urlKeywords: '',
		})
	})

	it('loads existing keyword filters and handles missing filter data', async () => {
		store.dispatch = vi.fn().mockResolvedValue({
			data: {
				filter: {
					titleKeywords: 'foo',
					bodyKeywords: 'bar',
					urlKeywords: 'baz',
				},
			},
		})
		wrapper = shallowMount(FeedSettingsDialog, {
			props: { feed: feeds[0] },
			global: { plugins: [store] },
		})
		await flushPromises()

		expect(wrapper.vm.filterForm).toEqual({
			titleKeywords: 'foo',
			bodyKeywords: 'bar',
			urlKeywords: 'baz',
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_GET_FILTER, { feed: feeds[0] })
	})

	it('reports an error when existing keyword filters cannot be loaded', async () => {
		store.dispatch = vi.fn().mockRejectedValue(new Error('network'))
		wrapper = shallowMount(FeedSettingsDialog, {
			props: { feed: feeds[0] },
			global: { plugins: [store] },
		})
		await flushPromises()

		expect(wrapper.vm.filterError).toBe('Unable to update keyword filters. Please try again.')
	})

	it('clears filter fields locally until the form is saved', () => {
		wrapper.vm.filterForm.titleKeywords = 'foo'
		wrapper.vm.filterForm.bodyKeywords = 'bar'
		wrapper.vm.filterForm.urlKeywords = 'baz'
		store.dispatch.mockClear()

		wrapper.vm.clearFilter()

		expect(wrapper.vm.filterForm).toEqual({
			titleKeywords: '',
			bodyKeywords: '',
			urlKeywords: '',
		})
		expect(store.dispatch).not.toHaveBeenCalled()
	})

	it('shows the scraping warning only when full-text scraping is enabled', () => {
		const enabledWrapper = shallowMount(FeedSettingsDialog, {
			props: {
				feed: { ...feeds[0], fullTextEnabled: true },
			},
			global: {
				plugins: [createStore()],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
					},
				},
			},
		})

		expect(enabledWrapper.findAllComponents({ name: 'NcNoteCard' })).toHaveLength(1)
		expect(wrapper.findAllComponents({ name: 'NcNoteCard' })).toHaveLength(0)
	})

	it('shows filter errors without closing', async () => {
		store.dispatch = vi.fn().mockRejectedValue({
			response: { data: { message: 'Validation failed' } },
		})
		wrapper = shallowMount(FeedSettingsDialog, {
			props: { feed: feeds[0] },
			global: { plugins: [store] },
		})

		await wrapper.vm.saveFilter()
		expect(wrapper.vm.filterError).toBe('Validation failed')
	})

	it('classifies preset and custom unread ages', () => {
		expect(wrapper.vm.getUnreadAgeOption({ maxUnreadAge: 0 }).value).toBe(0)
		expect(wrapper.vm.getUnreadAgeOption({ maxUnreadAge: 7 }).value).toBe(7)
		expect(wrapper.vm.getUnreadAgeOption({ maxUnreadAge: 2 }).value).toBe(-1)
	})

	it('updates unread-age settings', () => {
		wrapper.vm.setUnreadAgeOption({ value: 14 })
		wrapper.vm.setCustomUnreadAge('21')

		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_MAX_UNREAD_AGE, {
			feed: feeds[0],
			maxUnreadAge: 14,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_MAX_UNREAD_AGE, {
			feed: feeds[0],
			maxUnreadAge: 21,
		})
	})

	it('handles custom unread-age selection and invalid values', () => {
		wrapper.vm.setUnreadAgeOption(null)
		wrapper.vm.setUnreadAgeOption({ value: -1 })
		wrapper.vm.setCustomUnreadAge('invalid')

		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_MAX_UNREAD_AGE, {
			feed: feeds[0],
			maxUnreadAge: 2,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_MAX_UNREAD_AGE, {
			feed: feeds[0],
			maxUnreadAge: 1,
		})
	})

	it('dispatches the dialog toggle updates', async () => {
		await wrapper.vm.setPreventUpdate(true)
		await wrapper.vm.setUpdateMode(FEED_UPDATE_MODE.SILENT)
		await wrapper.vm.setFullText(true)

		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PREVENT_UPDATE, {
			feed: feeds[0],
			preventUpdate: true,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, {
			feed: feeds[0],
			updateMode: FEED_UPDATE_MODE.SILENT,
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, {
			feed: feeds[0],
			fullTextEnabled: true,
		})
	})

	it('saves filters and title before closing', async () => {
		wrapper.vm.title = 'Renamed feed'
		await wrapper.vm.saveAndClose()

		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SAVE_FILTER, {
			feed: feeds[0],
			titleKeywords: '',
			bodyKeywords: '',
			urlKeywords: '',
		})
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_TITLE, {
			feed: feeds[0],
			title: 'Renamed feed',
		})
		expect(wrapper.emitted('close')).toBeTruthy()
	})

	it('does not save a title or close when filter saving fails', async () => {
		store.dispatch = vi.fn()
			.mockResolvedValueOnce({})
			.mockRejectedValueOnce(new Error('save failed'))
		wrapper = shallowMount(FeedSettingsDialog, {
			props: { feed: feeds[0] },
			global: { plugins: [store] },
		})
		await flushPromises()
		wrapper.vm.title = 'Renamed feed'

		await wrapper.vm.saveAndClose()

		expect(store.dispatch).not.toHaveBeenCalledWith(ACTIONS.FEED_SET_TITLE, expect.anything())
		expect(wrapper.emitted('close')).toBeFalsy()
	})

	it('deletes a feed only after confirmation', async () => {
		vi.spyOn(window, 'confirm').mockReturnValueOnce(false).mockReturnValueOnce(true)

		await wrapper.vm.deleteFeed()
		expect(store.dispatch).not.toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed: feeds[0] })

		await wrapper.vm.deleteFeed()
		expect(store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed: feeds[0] })
		expect(wrapper.emitted('close')).toBeTruthy()
	})

	it('emits move and close from the rendered dialog actions', async () => {
		const renderedWrapper = mount(FeedSettingsDialog, {
			props: { feed: feeds[0] },
			global: {
				plugins: [store],
				stubs: {
					NcModal: {
						template: '<div><slot /></div>',
					},
				},
			},
		})

		const moveButton = renderedWrapper.findAllComponents({ name: 'NcButton' })
			.find((button) => button.text().includes('Move to folder'))!
		const cancelButton = renderedWrapper.findAllComponents({ name: 'NcButton' })
			.find((button) => button.text() === 'Cancel')!
		await moveButton.trigger('click')
		await cancelButton.trigger('click')

		expect(renderedWrapper.emitted('move')?.[0]).toEqual([feeds[0]])
		expect(renderedWrapper.emitted('close')).toBeTruthy()
	})
})
