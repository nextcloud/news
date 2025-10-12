import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
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
			},
		})
	})

	beforeEach(() => {
		dispatchStub.mockReset()
		commitStub.mockReset()
	})

	it('should send SET_SELECTED_ITEM with undefined id', () => {
		(wrapper.vm as any).clearSelected()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
	})

	it('should format date to match locale', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDate(epoch / 1000)

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
		const feed = (wrapper.vm as any).feed

		expect(feed).toEqual(mockFeed)
	})

	it('should toggle starred state', () => {
		wrapper.vm.$props.item.starred = true;

		(wrapper.vm as any).toggleStarred(wrapper.vm.$props.item)
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.UNSTAR_ITEM, {
			item: wrapper.vm.$props.item,
		})

		wrapper.vm.$props.item.starred = false;

		(wrapper.vm as any).toggleStarred(wrapper.vm.$props.item)
		expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.STAR_ITEM, {
			item: wrapper.vm.$props.item,
		})
	})

	it('should send SET_PLAYING_ITEM with item', () => {
		const item = { id: 123 };
		(wrapper.vm as any).playAudio(item)

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_PLAYING_ITEM, item)
	})

	it('should stop all audio elements in page when playing video', () => {
		const pauseStub = vi.fn()
		document.getElementsByTagName = vi.fn().mockReturnValue([{ pause: pauseStub }]);

		(wrapper.vm as any).stopAudio()

		expect(pauseStub).toBeCalled()
	})
})
