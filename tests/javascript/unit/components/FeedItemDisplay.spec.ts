import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import FeedItemDisplay from '../../../../src/components/FeedItemDisplay.vue'
import { ACTIONS, MUTATIONS } from '../../../../src/store'

describe('FeedItemDisplay.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	let wrapper: Wrapper<FeedItemDisplay>

	const mockItem = {
		feedId: 1,
		title: 'feed item',
		pubDate: Date.now() / 1000,
	}
	const mockFeed = {
		id: 1,
	}

	const dispatchStub = jest.fn()
	const commitStub = jest.fn()
	beforeAll(() => {
		wrapper = shallowMount(FeedItemDisplay, {
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
					commit: commitStub,
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
		const formattedDate = (wrapper.vm as any).formatDate(epoch)

		expect(formattedDate).toEqual(new Date(epoch).toLocaleString())
	})

	it('should format datetime to match international standard', () => {
		const epoch = Date.now() // Provide an epoch timestamp
		const formattedDate = (wrapper.vm as any).formatDatetime(epoch)

		expect(formattedDate).toEqual(new Date(epoch).toISOString())
	})

	it('should retrieve feed by ID', () => {
		const feed = (wrapper.vm as any).getFeed(mockFeed.id)

		expect(feed).toEqual(mockFeed)
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

	// TODO: Audio/Video tests
})
