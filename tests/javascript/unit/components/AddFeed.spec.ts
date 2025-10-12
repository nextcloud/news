import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import AddFeed from '../../../../src/components/AddFeed.vue'
import { FEED_ACTION_TYPES } from '../../../../src/store/feed'

describe('AddFeed.vue', () => {
	'use strict'

	const mockDispatch = vi.fn()
	const mockStore = {
		state: {
			folders: { folders: [] },
			feeds: { feeds: [] },
		},
		dispatch: mockDispatch,
	}

	let wrapper: any
	beforeEach(() => {
		wrapper = shallowMount(AddFeed, {
			global: {
				mocks: {
					$route: {
						query: {
							subscribe_to: undefined,
						},
					},
					$store: mockStore,
				},
			},
		})
	})

	it('should initialize with default values', () => {
		expect(wrapper.vm.$data.createNewFolder).toBeFalsy()
		expect(wrapper.vm.$data.autoDiscover).toBeTruthy()
		expect(wrapper.vm.$data.withBasicAuth).toBeFalsy()
	})

	it('should dispatch ADD_FEED action to store', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 200, data: { message: 'ok' } })

		await wrapper.vm.addFeed()

		expect(mockDispatch).toHaveBeenCalledWith(FEED_ACTION_TYPES.ADD_FEED, expect.anything())
		expect(wrapper.emitted('close'))
	})

	it('should dispatch ADD_FEED action but not emit close event on non-200 status', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 422, data: { message: 'no found' } })

		await wrapper.vm.addFeed()

		expect(mockDispatch).toHaveBeenCalledWith(FEED_ACTION_TYPES.ADD_FEED, expect.anything())
		expect(wrapper.emitted('close')).toBeUndefined()
	})

	it('should check if feed url exists and return true', () => {
		wrapper.vm.$data.feedUrl = ''
		let response = wrapper.vm.feedUrlExists()

		expect(response).toBeFalsy()

		wrapper.vm.$data.feedUrl = 'http://example.com'
		response = wrapper.vm.feedUrlExists()

		expect(response).toBeFalsy()

		wrapper.vm.$data.feedUrl = 'http://example.com'
		wrapper.vm.$store.state.feeds.feeds = [{ url: 'http://example.com' }]
		response = wrapper.vm.feedUrlExists()

		expect(response).toBeTruthy()
	})

	it('should check if folder name exists when creating folder and return true', () => {
		wrapper.vm.$data.newFolderName = ''
		let response = wrapper.vm.folderNameExists()

		expect(response).toBeFalsy()

		wrapper.vm.$data.newFolderName = 'test'
		response = wrapper.vm.folderNameExists()

		expect(response).toBeFalsy()

		wrapper.vm.$data.newFolderName = 'test'
		wrapper.vm.$store.state.folders.folders = [{ name: 'test' }]
		response = wrapper.vm.folderNameExists()

		expect(response).toBeFalsy()

		wrapper.vm.$data.newFolderName = 'test'
		wrapper.vm.$data.createNewFolder = 'test'
		wrapper.vm.$store.state.folders.folders = [{ name: 'test' }]
		response = wrapper.vm.folderNameExists()

		expect(response).toBeTruthy()
	})
})
