import { showError } from '@nextcloud/dialogs'
import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import MoveFeed from '../../../../src/components/MoveFeed.vue'
import { FEED_ACTION_TYPES } from '../../../../src/store/feed.ts'

vi.mock('@nextcloud/dialogs')

describe('MoveFeed.vue', () => {
	'use strict'

	const mockDispatch = vi.fn()
	const mockStore = {
		state: {
			folders: {
				folders: [
					{ id: 2, name: 'Folder 2' },
				],
			},
		},
		dispatch: mockDispatch,
	}

	let wrapper: any

	beforeEach(() => {
		vi.clearAllMocks()
		wrapper = shallowMount(MoveFeed, {
			props: {
				feed: { id: 1, folderId: 1 },
			},
			global: {
				mocks: {
					$store: mockStore,
				},
			},
		})
		wrapper.vm.$data.folder = { id: 2, name: 'Folder 2' }
	})

	it('dispatches the move and refreshes feeds on success', async () => {
		mockDispatch
			.mockResolvedValueOnce({ status: 204 })
			.mockResolvedValueOnce(undefined)

		await wrapper.vm.moveFeed()

		expect(mockDispatch).toHaveBeenNthCalledWith(1, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(mockDispatch).toHaveBeenNthCalledWith(2, FEED_ACTION_TYPES.FETCH_FEEDS)
		expect(showError).not.toHaveBeenCalled()
		expect(wrapper.emitted('close')).toHaveLength(1)
	})

	it('shows an error and keeps the dialog open when the move fails', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 500 })

		await wrapper.vm.moveFeed()

		expect(mockDispatch).toHaveBeenCalledTimes(1)
		expect(mockDispatch).toHaveBeenCalledWith(FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(showError).toHaveBeenCalledWith('Unable to move feed. Please try again later or check your connection.')
		expect(wrapper.emitted('close')).toBeUndefined()
	})
})
