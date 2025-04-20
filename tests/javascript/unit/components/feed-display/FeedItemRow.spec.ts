import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import FeedItemRow from '../../../../../src/components/feed-display/FeedItemRow.vue'
import { ACTIONS } from '../../../../../src/store'

describe('FeedItemRow.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	let wrapper: Wrapper<FeedItemRow>

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
		unread: true,
	}
	const mockFeed = {
		id: 1,
	}

	const dispatchStub = jest.fn()
	beforeAll(() => {
		wrapper = shallowMount(FeedItemRow, {
			propsData: {
				item: mockItem,
			},
			localVue,
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
					commit: jest.fn(),
				},
			},
		})
	})

	beforeEach(() => {
		dispatchStub.mockReset()
	})

	it('should initialize without expanded and without keepUnread', () => {
		expect(wrapper.vm.$props.item.keepUnread).toBeFalsy()
	})

	it('should format date to match locale', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDate(epoch / 1000)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString())
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

		expect(relativeTimestamp).toEqual('seconds ago')

		pastTimestamp = currentTimestamp - 1000 * 60 * 10 // 10 minutes ago

		relativeTimestamp = (wrapper.vm as any).formatDateRelative(pastTimestamp / 1000)

		expect(relativeTimestamp).toEqual('10 minutes ago')

		pastTimestamp = currentTimestamp - 1000 * 3600 // 1 hour ago

		relativeTimestamp = (wrapper.vm as any).formatDateRelative(pastTimestamp / 1000)

		expect(relativeTimestamp).toEqual('an hour ago')
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
})
