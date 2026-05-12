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
				feeds: [{ id: 1, folderId: 1 }],
			},
			global: {
				mocks: {
					$store: mockStore,
				},
			},
		})
		wrapper.vm.$data.folder = { id: 2, name: 'Folder 2' }
	})

	it('dispatches the single move and refreshes feeds on success', async () => {
		mockDispatch
			.mockResolvedValueOnce({ status: 204 })
			.mockResolvedValueOnce(undefined)

		await wrapper.vm.moveFeeds()

		expect(mockDispatch).toHaveBeenNthCalledWith(1, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(mockDispatch).toHaveBeenNthCalledWith(2, FEED_ACTION_TYPES.FETCH_FEEDS)
		expect(showError).not.toHaveBeenCalled()
		expect(wrapper.emitted('close')).toHaveLength(1)
	})

	it('shows an error and keeps the dialog open when the single move fails', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 500 })

		await wrapper.vm.moveFeeds()

		expect(mockDispatch).toHaveBeenCalledTimes(1)
		expect(mockDispatch).toHaveBeenCalledWith(FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(showError).toHaveBeenCalledWith('Unable to move feed. Please try again later or check your connection.')
		expect(wrapper.emitted('close')).toBeUndefined()
	})

	it('dispatches the batched move and refreshes feeds on success', async () => {
		mockDispatch
			.mockResolvedValueOnce({ status: 204 })
			.mockResolvedValueOnce({ status: 204 })
			.mockResolvedValueOnce(undefined)

		await wrapper.setProps({
			feeds: [{ id: 1, folderId: 1 }, { id: 2 }],
		})
		await wrapper.vm.moveFeeds()

		expect(mockDispatch).toHaveBeenNthCalledWith(1, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(mockDispatch).toHaveBeenNthCalledWith(2, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 2, folderId: 2 })
		expect(mockDispatch).toHaveBeenNthCalledWith(3, FEED_ACTION_TYPES.FETCH_FEEDS)
		expect(showError).not.toHaveBeenCalled()
		expect(wrapper.emitted('close')).toHaveLength(1)
	})

	it('shows an error and closes the dialog when the batched move fails', async () => {
		mockDispatch
			.mockResolvedValueOnce({ status: 204 })
			.mockResolvedValueOnce({ status: 500 })
			.mockResolvedValueOnce(undefined)

		await wrapper.setProps({
			feeds: [{ id: 1, folderId: 1 }, { id: 2 }],
		})
		await wrapper.vm.moveFeeds()

		expect(mockDispatch).toHaveBeenNthCalledWith(1, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 1, folderId: 2 })
		expect(mockDispatch).toHaveBeenNthCalledWith(2, FEED_ACTION_TYPES.MOVE_FEED, { feedId: 2, folderId: 2 })
		expect(showError).toHaveBeenCalledWith('Some selected feeds could not be moved. Please try again later or check your connection.')
		expect(wrapper.emitted('close')).toHaveLength(1)
	})

	it('should not disable move feed button when multiple feeds selected', async () => {
		await wrapper.setProps({
			feeds: [{ id: 1, folderId: 1 }, { id: 2 }],
		})
		const disableMove = wrapper.vm.$options.computed?.disableMoveFeed.call(wrapper.vm)

		expect(disableMove).toBeFalsy()
	})

	it('should disable move feed button when selected folder is same than current feed folder', async () => {
		await wrapper.setProps({
			feeds: [{ id: 1, folderId: 2 }],
		})
		const disableMove = wrapper.vm.$options.computed?.disableMoveFeed.call(wrapper.vm)

		expect(disableMove).toBeTruthy()
	})

	it('should disable move feed button when no feeds are selected', async () => {
		await wrapper.setProps({
			feeds: [],
		})
		const disableMove = wrapper.vm.$options.computed?.disableMoveFeed.call(wrapper.vm)

		expect(disableMove).toBeTruthy()
	})
})
