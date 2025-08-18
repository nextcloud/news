import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import FeedItemRow from '../../../../../src/components/feed-display/FeedItemRow.vue'
import { ACTIONS, MUTATIONS } from '../../../../../src/store'

describe('FeedItemRow.vue', () => {
	'use strict'
	let wrapper: any
	let dispatchStub: any
	let commitStub: any

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
		unread: true,
	}
	const mockFeed = {
		id: 1,
	}

	beforeEach(() => {
		dispatchStub = vi.fn()
		commitStub = vi.fn()

		wrapper = shallowMount(FeedItemRow, {
			props: {
				item: mockItem,
				itemIndex: 1,
				itemCount: 1,
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

	it('should initialize without expanded and without keepUnread', () => {
		expect(wrapper.vm.$props.item.keepUnread).toBeFalsy()
	})

	it('should format date to match locale', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDate(epoch / 1000)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString(undefined, {
			year: "numeric",
			month: "2-digit",
			day: "2-digit",
			hour: "2-digit",
			minute: "2-digit",
			second: "2-digit",
		}))
	})

	it('should format datetime to match international standard', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDateISO(epoch / 1000)

		expect(formattedDate).toEqual(new Date(epoch).toISOString())
	})

	it('should calculate relative timestamp correctly', () => {
		const currentTimestamp = Date.now()
		let pastTimestamp = currentTimestamp - 1000 * 10 // 10 seconds ago

		let relativeTimestamp = (wrapper.vm as any).formatDateRelative(pastTimestamp / 1000)

		expect(relativeTimestamp).toEqual('10 seconds ago')

		pastTimestamp = currentTimestamp - 1000 * 60 * 10 // 10 minutes ago

		relativeTimestamp = (wrapper.vm as any).formatDateRelative(pastTimestamp / 1000)

		expect(relativeTimestamp).toEqual('10 minutes ago')

		pastTimestamp = currentTimestamp - 1000 * 3600 // 1 hour ago

		relativeTimestamp = (wrapper.vm as any).formatDateRelative(pastTimestamp / 1000)

		expect(relativeTimestamp).toEqual('1 hour ago')
	})

	it('should retrieve feed by ID', () => {
		const feed = (wrapper.vm as any).getFeed(mockFeed.id)

		expect(feed).toEqual(mockFeed)
	})

	describe('markRead', () => {
		it('should mark item as read when keepUnread is false', () => {
			wrapper.vm.$props.item.keepUnread = false;
			(wrapper.vm as any).markRead(wrapper.vm.$props.item)

			expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_READ, {
				item: wrapper.vm.$props.item,
			})
		})

		it('should not mark item as read when keepUnread is true', () => {
			wrapper.vm.$props.item.keepUnread = true;
			(wrapper.vm as any).markRead(wrapper.vm.$props.item)

			expect(dispatchStub).not.toHaveBeenCalled()
		})
	})

	it('toggles keepUnread state', () => {
		const initialKeepUnread = wrapper.vm.$props.item.keepUnread;
		(wrapper.vm as any).toggleKeepUnread(wrapper.vm.$props.item)
		const updatedKeepUnread = wrapper.vm.$props.item.keepUnread

		expect(updatedKeepUnread).toBe(!initialKeepUnread)
	})

	it('toggles starred state', () => {
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

	it('should commit selected item, mark as read, and emit show-details when clicked', async () => {
		const markReadSpy = vi.spyOn(wrapper.vm, 'markRead')

		await wrapper.trigger('click')

		expect(commitStub).toHaveBeenCalledWith(MUTATIONS.SET_SELECTED_ITEM, { id: mockItem.id, key: 'all' })
		expect(markReadSpy).toHaveBeenCalledWith(mockItem)
		expect(wrapper.emitted()).toHaveProperty('show-details')
	})
})
