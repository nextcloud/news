import { shallowMount, createLocalVue } from '@vue/test-utils'
import AddFeed from '../../../../src/components/AddFeed.vue'
import { FEED_ACTION_TYPES } from '../../../../src/store/feed'

describe('AddFeed.vue', () => {
	'use strict'

	const mockDispatch = jest.fn()
	const mockStore = {
		state: {
			folders: { folders: [] },
			feeds: { feeds: [] },
		},
		dispatch: mockDispatch,
	}

	let wrapper: any
	beforeEach(() => {
		const localVue = createLocalVue()
		wrapper = shallowMount(AddFeed, {
			localVue,
			mocks: {
				$route: {
					query: {
						subscribe_to: undefined,
					},
				},
				$store: mockStore,
			},
		})
	})

	it('should initialize with default values', () => {
		expect(wrapper.vm.$data.createNewFolder).toBeFalsy()
		expect(wrapper.vm.$data.autoDiscover).toBeTruthy()
		expect(wrapper.vm.$data.withBasicAuth).toBeFalsy()
	})

	it('should dispatch ADD_FEED action to store', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 200, data: { message: "ok" }})
		wrapper.vm.$emit = jest.fn()

		await wrapper.vm.addFeed()

		expect(wrapper.vm.$emit).toBeCalled()
		expect(mockDispatch).toBeCalled()
		expect(mockDispatch.mock.calls[0][0]).toEqual(FEED_ACTION_TYPES.ADD_FEED)
	})

	it('should dispatch ADD_FEED action but not emit close event on non-200 status', async () => {
		mockDispatch.mockResolvedValueOnce({ status: 422, data: { message: "no found" }})
		wrapper.vm.$emit = jest.fn()

		await wrapper.vm.addFeed()

		expect(wrapper.vm.$emit).not.toBeCalled()
		expect(mockDispatch).toBeCalled()
		expect(mockDispatch.mock.calls[0][0]).toEqual(FEED_ACTION_TYPES.ADD_FEED)
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
