import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'
import { mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import AppSettingsDialog from '../../../../../src/components/modals/AppSettingsDialog.vue'
import { DISPLAY_MODE, MEDIA_TYPE, SHOW_MEDIA, SPLIT_MODE } from '../../../../../src/enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../../../../src/store/index.ts'

vi.mock('@nextcloud/dialogs')

describe('AppSettingsDialog.vue', () => {
	'use strict'

	let wrapper: any

	const mediaOptions = {
		[MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.NEVER,
		[MEDIA_TYPE.IMAGES]: SHOW_MEDIA.NEVER,
		[MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.NEVER,
		[MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.NEVER,
	}

	const apiUrl = generateOcsUrl('/apps/provisioning_api/api/v1/config/users/news/')

	beforeEach(() => {
		vi.restoreAllMocks()
		wrapper = mount(AppSettingsDialog, {
			global: {
				mocks: {
					$store: {
						getters: {
							displaymode: 'compact',
							showAll: true,
							preventReadOnScroll: false,
							disableRefresh: true,
							oldestFirst: false,
							loading: false,
							mediaOptions,
						},
						dispatch: vi.fn(),
						commit: vi.fn(),
					},
				},
			},
		})
	})

	describe('updates settings via v-model when user click on a switch', () => {
		it('enable preventReadOnScroll', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Disable mark read through scrolling')
			await formBoxSwitch.setValue(true)
			expect(axios.post).toBeCalledWith(apiUrl + 'preventReadOnScroll', { configValue: '1' })
		})

		it('disable preventReadOnScroll', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Disable mark read through scrolling')
			await formBoxSwitch.setValue(false)
			expect(axios.post).toBeCalledWith(apiUrl + 'preventReadOnScroll', { configValue: '0' })
		})

		it('enable showAll', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Show all articles')
			await formBoxSwitch.setValue(true)
			expect(axios.post).toBeCalledWith(apiUrl + 'showAll', { configValue: '1' })
		})

		it('disable showAll', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Show all articles')
			await formBoxSwitch.setValue(false)
			expect(axios.post).toBeCalledWith(apiUrl + 'showAll', { configValue: '0' })
		})

		it('enable oldestFirst', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Reverse ordering (oldest on top)')
			await formBoxSwitch.setValue(true)
			expect(axios.post).toBeCalledWith(apiUrl + 'oldestFirst', { configValue: '1' })
		})

		it('disable oldestFirst', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Reverse ordering (oldest on top)')
			await formBoxSwitch.setValue(false)
			expect(axios.post).toBeCalledWith(apiUrl + 'oldestFirst', { configValue: '0' })
		})

		it('enable disableRefresh', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Disable automatic refresh')
			await formBoxSwitch.setValue(true)
			expect(axios.post).toBeCalledWith(apiUrl + 'disableRefresh', { configValue: '1' })
		})

		it('disable disableRefresh', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const formBoxSwitch = wrapper.findAllComponents({ name: 'NcFormBoxSwitch' })
				.find((c) => c.props('label') === 'Disable automatic refresh')
			await formBoxSwitch.setValue(false)
			expect(axios.post).toBeCalledWith(apiUrl + 'disableRefresh', { configValue: '0' })
		})
	})

	describe('updates displaymode via v-model when user selects a radio button', () => {
		it('set displaymode to default mode', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Default')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'displaymode', { configValue: DISPLAY_MODE.DEFAULT })
		})

		it('set displaymode to compact mode', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Compact')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'displaymode', { configValue: DISPLAY_MODE.COMPACT })
		})

		it('set displaymode to screenreader mode', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Screenreader')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'displaymode', { configValue: DISPLAY_MODE.SCREENREADER })
		})
	})

	describe('updates split-mode via v-model when user selects a radio button', () => {
		it('set split-mode to vertical', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Vertical')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'splitmode', { configValue: SPLIT_MODE.VERTICAL })
		})

		it('set split-mode to horizontal', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Horizontal')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'splitmode', { configValue: SPLIT_MODE.HORIZONTAL })
		})

		it('set split-mode to off', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = wrapper.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Off')
			await radioGroupButton.trigger('click')
			expect(axios.post).toBeCalledWith(apiUrl + 'splitmode', { configValue: SPLIT_MODE.OFF })
		})
	})

	describe('updates mediaThumbnail via v-model when user selects a radio button', () => {
		let radioGroups
		let radioGroup
		beforeEach(() => {
			radioGroups = wrapper.findAllComponents({ name: 'NcRadioGroup' })
			radioGroup = radioGroups.find((g) => g.props('label') === 'Show enclosure thumbnails')
		})
		it('set mediaThumbnail to always', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Always')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.ALWAYS })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaThumbnail to ask', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Ask')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.ASK })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaThumbnail to never', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Never')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.THUMBNAILS]: SHOW_MEDIA.NEVER })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})
	})

	describe('updates mediaImages via v-model when user selects a radio button', () => {
		let radioGroups
		let radioGroup
		beforeEach(() => {
			radioGroups = wrapper.findAllComponents({ name: 'NcRadioGroup' })
			radioGroup = radioGroups.find((g) => g.props('label') === 'Show enclosure images')
		})
		it('set mediaImages to always', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Always')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES]: SHOW_MEDIA.ALWAYS })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaImages to ask', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Ask')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES]: SHOW_MEDIA.ASK })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaImages to never', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Never')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES]: SHOW_MEDIA.NEVER })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})
	})

	describe('updates mediaImagesBody via v-model when user selects a radio button', () => {
		let radioGroups
		let radioGroup
		beforeEach(() => {
			radioGroups = wrapper.findAllComponents({ name: 'NcRadioGroup' })
			radioGroup = radioGroups.find((g) => g.props('label') === 'Show images in the article text')
		})
		it('set mediaImagesBody to always', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Always')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ALWAYS })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaImagesBody to ask', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Ask')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.ASK })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaImagesBody to never', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Never')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IMAGES_BODY]: SHOW_MEDIA.NEVER })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})
	})

	describe('updates mediaIframesBody via v-model when user selects a radio button', () => {
		let radioGroups
		let radioGroup
		beforeEach(() => {
			radioGroups = wrapper.findAllComponents({ name: 'NcRadioGroup' })
			radioGroup = radioGroups.find((g) => g.props('label') === 'Show iframes in the article text')
		})
		it('set mediaIframesBody to always', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Always')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ALWAYS })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaIframesBody to ask', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Ask')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.ASK })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})

		it('set mediaIframesBody to never', async () => {
			vi.spyOn(axios, 'post').mockResolvedValue({ data: { ocs: { meta: { status: 'ok' } } } })

			const radioGroupButton = radioGroup.findAllComponents({ name: 'NcRadioGroupButton' })
				.find((c) => c.props('label') === 'Never')
			await radioGroupButton.trigger('click')
			const configValue = JSON.stringify({ ...mediaOptions, [MEDIA_TYPE.IFRAMES_BODY]: SHOW_MEDIA.NEVER })
			expect(axios.post).toBeCalledWith(apiUrl + 'mediaOptions', { configValue })
		})
	})

	describe('test opml import/export', () => {
		it('should show error when no file is selected', async () => {
			const event = { target: { files: [] } }

			await wrapper.vm.importOpml.call(wrapper, event)

			const value = { type: 'error', message: 'Please select a valid OPML file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value })
		})

		it('should show success when status is 200 and file is valid', async () => {
			const mockFile = new File(['{}'], 'feeds.opml', { type: 'application/xml' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'ok' },
			})

			await wrapper.vm.importOpml.call(wrapper, event)

			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: true })
			const value = { type: 'success', message: 'File successfully uploaded' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FOLDERS)
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEEDS)
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: false })
		})

		it('should show backend error message when status 200 but backend returns error', async () => {
			const mockFile = new File(['{}'], 'feeds.opml', { type: 'application/xml' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'error', message: 'Error importing feeds' },
			})

			await wrapper.vm.importOpml.call(wrapper, event)

			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: true })
			const value = { type: 'warning', message: 'Error importing feeds' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value })
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: false })
		})

		it('should show error message when not status 200', async () => {
			const mockFile = new File(['{}'], 'feeds.opml', { type: 'application/xml' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 412,
			})

			await wrapper.vm.importOpml.call(wrapper, event)

			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: true })
			const value = { type: 'error', message: 'Error uploading the opml file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value })
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_LOADING, { value: false })
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			const mockFile = new File(['{}'], 'feeds.opml')
			const event = { target: { files: [mockFile] } }

			axios.post.mockRejectedValue('network error')

			await wrapper.vm.importOpml.call(wrapper, event)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})

		it('should download file when status is 200', async () => {
			const blob = { slice: vi.fn() }
			axios.get.mockResolvedValue({ status: 200, data: blob })

			const clickMock = vi.fn()

			vi.spyOn(document, 'createElement').mockReturnValue({
				href: '',
				download: '',
				click: clickMock,
			})

			vi.spyOn(URL, 'createObjectURL').mockReturnValue('blob://test')

			await wrapper.vm.exportOpml.call(wrapper)

			expect(clickMock).toHaveBeenCalled()
		})

		it('should show error when status is not 200', async () => {
			axios.get.mockResolvedValue({ status: 500 })

			await wrapper.vm.exportOpml.call(wrapper)

			const value = { type: 'error', message: 'Error retrieving the opml file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value })
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			axios.get.mockRejectedValue('network error')

			await wrapper.vm.exportOpml.call(wrapper)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})
	})

	describe('test articles import/export', () => {
		it('should show error when no file is selected', async () => {
			const event = { target: { files: [] } }

			await wrapper.vm.importArticles.call(wrapper, event)

			const value = { type: 'error', message: 'Please select a valid json file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value })
		})

		it('should show success when status is 200 and file is valid', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'ok' },
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			const value = { type: 'success', message: 'File successfully uploaded' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEEDS)
		})

		it('should show backend error message when status 200 but backend returns error', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'error', message: 'Error importing articles' },
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			const value = { type: 'warning', message: 'Error importing articles' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value })
		})

		it('should show error message when not status 200', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 412,
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			const value = { type: 'error', message: 'Error uploading the json file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value })
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			const mockFile = new File(['{}'], 'articles.json')
			const event = { target: { files: [mockFile] } }

			axios.post.mockRejectedValue('network error')

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})

		it('should download file when status is 200', async () => {
			const blob = { slice: vi.fn() }
			axios.get.mockResolvedValue({ status: 200, data: blob })

			const clickMock = vi.fn()

			vi.spyOn(document, 'createElement').mockReturnValue({
				href: '',
				download: '',
				click: clickMock,
			})

			vi.spyOn(URL, 'createObjectURL').mockReturnValue('blob://test')

			await wrapper.vm.exportArticles.call(wrapper)

			expect(clickMock).toHaveBeenCalled()
		})

		it('should show error when status is not 200', async () => {
			axios.get.mockResolvedValue({ status: 500 })

			await wrapper.vm.exportArticles.call(wrapper)

			const value = { type: 'error', message: 'Error retrieving the json file' }
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value })
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			axios.get.mockRejectedValue('network error')

			await wrapper.vm.exportArticles.call(wrapper)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})
	})

	afterEach(() => {
		vi.clearAllMocks()
	})
})
