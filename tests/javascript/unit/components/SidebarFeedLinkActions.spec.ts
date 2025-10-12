import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import SidebarFeedLinkActions from '../../../../src/components/SidebarFeedLinkActions.vue'
import { FEED_ORDER, FEED_UPDATE_MODE } from '../../../../src/enums/index.ts'
import { ACTIONS } from '../../../../src/store/index.ts'

describe('SidebarFeedLinkActions.vue', () => {
	'use strict'

	let wrapper: any

	const feeds = [{
		id: 1, title: 'first',
	}, {
		id: 2, title: 'second', folderId: 123,
	}]

	beforeAll(() => {
		wrapper = shallowMount(SidebarFeedLinkActions, {
			props: {
				feedId: 1,
			},
			global: {
				mocks: {
					$store: {
						state: {
							feeds,
							folders: [],
						},
						getters: {
							feeds,
						},
						dispatch: vi.fn(),
						commit: vi.fn(),
					},
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
			window.prompt = vi.fn().mockReturnValue('test');

			(wrapper.vm as any).rename()

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_SET_TITLE, { feed: feeds[0], title: 'test' })
		})

		it('should dispatch message to store with feed object on delete feed', () => {
			window.confirm = vi.fn().mockReturnValue(true);

			(wrapper.vm as any).deleteFeed()

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed: feeds[0] })
		})
	})

	afterEach(() => {
		vi.clearAllMocks()
	})
})
