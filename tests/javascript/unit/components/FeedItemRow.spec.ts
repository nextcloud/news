import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import FeedItemRow from '../../../../src/components/FeedItemRow.vue'
import { ACTIONS } from '../../../../src/store'

describe('FeedItemRow.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	let wrapper: Wrapper<FeedItemRow>

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
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
		expect(wrapper.vm.$data.keepUnread).toBeFalsy()
	})

	it('should expand when clicked', async () => {
		await wrapper.find('.feed-item-row').trigger('click')

		// expect(wrapper.vm.$data.expanded).toBe(true)
	})

	it('should format date correctly', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDate(epoch)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString())
	})

	it('should format datetime correctly', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDatetime(epoch)

		expect(formattedDate).toEqual(new Date(epoch).toISOString())
	})

	it('should calculate relative timestamp correctly', () => {
		const currentTimestamp = Date.now()
		let pastTimestamp = currentTimestamp - 1000 * 10 // 10 seconds ago

		let relativeTimestamp = (wrapper.vm as any).getRelativeTimestamp(pastTimestamp)

		expect(relativeTimestamp).toEqual('10 seconds')

		pastTimestamp = currentTimestamp - 1000 * 60 * 10 // 10 minutes ago

		relativeTimestamp = (wrapper.vm as any).getRelativeTimestamp(pastTimestamp)

		expect(relativeTimestamp).toEqual('10 minutes ago')
	})

	it('should retrieve feed by ID', () => {
		const feed = (wrapper.vm as any).getFeed(mockFeed.id)

		expect(feed).toEqual(mockFeed)
	})

	describe('markRead', () => {
		it('should mark item as read when keepUnread is false', () => {
			wrapper.vm.$data.keepUnread = false;
			(wrapper.vm as any).markRead(wrapper.vm.$props.item)

			expect(dispatchStub).toHaveBeenCalledWith(ACTIONS.MARK_READ, {
				item: wrapper.vm.$props.item,
			})
		})

		it('should not mark item as read when keepUnread is true', () => {
			wrapper.vm.$data.keepUnread = true;
			(wrapper.vm as any).markRead(wrapper.vm.$data.item)

			expect(dispatchStub).not.toHaveBeenCalled()
		})
	})

	it('toggles keepUnread state', () => {
		const initialKeepUnread = wrapper.vm.$data.keepUnread;
		(wrapper.vm as any).toggleKeepUnread(wrapper.vm.$data.item)
		const updatedKeepUnread = wrapper.vm.$data.keepUnread

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
