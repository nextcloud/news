import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import FeedItemDisplay from '../../../../../src/components/feed-display/FeedItemDisplay.vue'
import { ACTIONS, MUTATIONS } from '../../../../../src/store/index.ts'

describe('FeedItemDisplay.vue', () => {
	'use strict'
	let wrapper: any

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
	}
	const mockFeed = {
		id: 1,
	}

	const dispatchStub = vi.fn()
	const commitStub = vi.fn()
	beforeAll(() => {
		wrapper = shallowMount(FeedItemDisplay, {
			props: {
				item: mockItem,
				fetchKey: 'all',
			},
			global: {
				mocks: {
					$store: {
						getters: {
							feeds: [mockFeed],
						},
						state: {
							feeds: [],
							folders: [],
						},
						dispatch: dispatchStub,
						commit: commitStub,
					},
				},
				stubs: {
					NcChip: {
						name: 'NcChip',
						props: ['text', 'variant', 'noClose'],
						template: '<span class="nc-chip">{{ text }}</span>',
					},

				},
			},
		})
	})

	beforeEach(() => {
		dispatchStub.mockReset()
		commitStub.mockReset()
	})

	it('should format date to match locale', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = wrapper.vm.formatDate(epoch / 1000)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString(OC.getLanguage(), {
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: 'numeric',
			minute: '2-digit',
			second: '2-digit',
		}))
	})

	it('should retrieve feed by ID', () => {
		const feed = wrapper.vm.feed

		expect(feed).toEqual(mockFeed)
	})

	it('should focus on new selected item when using screen reader mode', async () => {
		const el = { focus: vi.fn() }
		Object.defineProperty(wrapper.vm.$refs, 'titleLink', { value: el, configurable: true })

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		wrapper.vm.$options.watch.isSelected.call(wrapper.vm, true)
		await nextTick()

		expect(el.focus).toHaveBeenCalled()
	})

	it('should not focus on new selected item when not using screen reader mode', async () => {
		const el = { focus: vi.fn() }
		Object.defineProperty(wrapper.vm.$refs, 'titleLink', { value: el, configurable: true })

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		wrapper.vm.$options.watch.isSelected.call(wrapper.vm, true)
		await nextTick()

		expect(el.focus).not.toHaveBeenCalled()
	})

	it('should send SET_SELECTED_ITEM with undefined id', () => {
		wrapper.vm.clearSelected()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
	})

	it('should send SET_SELECTED_ITEM with item on focus when using screen reader mode and item is not selected', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(false)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem.id, key: 'all' })
	})

	it('should not send SET_SELECTED_ITEM with item on focus when not using screen reader mode', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(false)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)

		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(false)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(true)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)
	})

	it('should not send SET_SELECTED_ITEM with item on focus when item is already selected', () => {
		vi.spyOn(wrapper.vm, 'screenReaderMode', 'get').mockReturnValue(true)
		vi.spyOn(wrapper.vm, 'isSelected', 'get').mockReturnValue(true)
		wrapper.vm.selectItemOnFocus()

		expect(commitStub).not.toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM)
	})

	it('should toggle starred state', () => {
		wrapper.vm.$props.item.starred = true

		wrapper.vm.toggleStarred()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.UNSTAR_ITEM, {
			item: wrapper.vm.$props.item,
		})

		wrapper.vm.$props.item.starred = false

		wrapper.vm.toggleStarred()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.STAR_ITEM, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should toggle unread state', () => {
		wrapper.vm.$props.item.keepUnread = false
		wrapper.vm.$props.item.unread = true

		wrapper.vm.toggleRead()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_READ, {
			item: wrapper.vm.$props.item,
		})

		wrapper.vm.$props.item.unread = false

		wrapper.vm.toggleRead()
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_UNREAD, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should not toggle unread state if keepUnread is set', () => {
		wrapper.vm.$props.item.keepUnread = true
		wrapper.vm.$props.item.unread = true

		wrapper.vm.toggleRead()
		expect(dispatchStub).not.toHaveBeenCalledWith(ACTIONS.MARK_READ, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should set showShareMenu to false', () => {
		wrapper.vm.showShareMenu = true

		wrapper.vm.closeShareMenu()
		expect(wrapper.vm.showShareMenu).toEqual(false)
	})

	it('should return the correct media type', () => {
		let mime = wrapper.vm.getMediaType('audio/mp4')
		expect(mime).toEqual('audio')

		mime = wrapper.vm.getMediaType('video/mpeg')
		expect(mime).toEqual('video')

		mime = wrapper.vm.getMediaType('application/pdf')
		expect(mime).toEqual(false)
	})

	it('should send SET_PLAYING_ITEM with item', () => {
		const item = { id: 123 }
		wrapper.vm.playAudio(item)

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_PLAYING_ITEM, item)
	})

	it('should stop all audio elements in page when playing video', () => {
		const pauseStub = vi.fn()
		document.getElementsByTagName = vi.fn().mockReturnValue([{ pause: pauseStub }])

		wrapper.vm.stopAudio()

		expect(pauseStub).toBeCalled()
	})

	it('should emit "prevItem" when calling prevItem', () => {
		wrapper.vm.prevItem()

		expect(wrapper.emitted()).toHaveProperty('prevItem')
		expect(wrapper.emitted('prevItem')!.length).toBe(1)
	})

	it('should emit "nextItem" when calling nextItem', () => {
		wrapper.vm.nextItem()

		expect(wrapper.emitted()).toHaveProperty('nextItem')
		expect(wrapper.emitted('nextItem')!.length).toBe(1)
	})

	it('should emit "showDetails" when calling closeDetails', () => {
		wrapper.vm.closeDetails()

		expect(wrapper.emitted()).toHaveProperty('showDetails')
		expect(wrapper.emitted('showDetails')!.length).toBe(1)
	})

	it('should show no chips when item has no categories', () => {
		const chips = wrapper.findAllComponents({ name: 'NcChip' })

		expect(chips.length).toBe(0)
	})

	it('should show no chips when item.categories is empty', async () => {
		await wrapper.setProps({
			item: { ...mockItem, categories: [] },
		})
		const chips = wrapper.findAllComponents({ name: 'NcChip' })

		expect(chips.length).toBe(0)
	})

	it('should show three chips with text from item.categories', async () => {
		await wrapper.setProps({
			item: { ...mockItem, categories: ['Nextcloud', 'News', 'Reader'] },
		})
		const chips = wrapper.findAllComponents({ name: 'NcChip' })
		expect(chips.length).toBe(3)

		expect(chips[0].text()).toBe('Nextcloud')
		expect(chips[1].text()).toBe('News')
		expect(chips[2].text()).toBe('Reader')
	})
})
