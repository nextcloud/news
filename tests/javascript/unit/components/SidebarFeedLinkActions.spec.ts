import { ACTIONS } from '../../../../src/store'
import { Wrapper, shallowMount, createLocalVue } from '@vue/test-utils'

import SidebarFeedLinkActions from '../../../../src/components/SidebarFeedLinkActions.vue'
import { FEED_UPDATE_MODE, FEED_ORDER } from '../../../../src/dataservices/feed.service'

describe('SidebarFeedLinkActions.vue', () => {
	'use strict'

	let wrapper: Wrapper<SidebarFeedLinkActions>

	const feeds = [{
		id: 1, title: 'first',
	}, {
		id: 2, title: 'second', folderId: 123,
	}]

	beforeAll(() => {
		const localVue = createLocalVue()
		wrapper = shallowMount(SidebarFeedLinkActions, {
			localVue,
			propsData: {
				feedId: 1,
			},
			mocks: {
				$store: {
					state: {
						feeds,
						folders: [],
					},
					getters: {
						feeds,
					},
					dispatch: jest.fn(),
				},
			},
		})
	})

	beforeEach(() => {
		(wrapper.vm as any).$store.dispatch.mockReset()
	})

	describe('User Actions', () => {
		it('should dispatch message to store with feed object', () => {
			(wrapper.vm as any).markRead()

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_MARK_READ, { feed: feeds[0] })
		})

		it('should dispatch message to store with feed object and pinned', () => {
			(wrapper.vm as any).setPinned(true)

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_PINNED, { feed: feeds[0], pinned: true })
		})

		it('should dispatch message to store with feed object and fullTextEnabled', () => {
			(wrapper.vm as any).setOrdering(FEED_ORDER.NEWEST)

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_ORDERING, { feed: feeds[0], ordering: FEED_ORDER.NEWEST })
		})

		it('should dispatch message to store with feed object and fullTextEnabled', () => {
			(wrapper.vm as any).setFullText(true)

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_FULL_TEXT, { feed: feeds[0], fullTextEnabled: true })
		})

		it('should dispatch message to store with feed object and new updateMode', () => {
			(wrapper.vm as any).setUpdateMode(FEED_UPDATE_MODE.IGNORE)

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_UPDATE_MODE, { feed: feeds[0], updateMode: FEED_UPDATE_MODE.IGNORE })
		})

		it('should dispatch message to store with feed object on rename feed', () => {
			window.prompt = jest.fn().mockReturnValue('test');

			(wrapper.vm as any).rename()

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_TITLE, { feed: feeds[0], title: 'test' })
		})

		it('should dispatch message to store with feed object on delete feed', () => {
			window.confirm = jest.fn().mockReturnValue(true);

			(wrapper.vm as any).deleteFeed()

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed: feeds[0] })
		})
	})

	afterEach(() => {
		jest.clearAllMocks()
	})
})
