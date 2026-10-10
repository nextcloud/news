import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import App from '../../../../src/App.vue'
import { MUTATIONS } from '../../../../src/store/index.ts'

describe('FeedItemDisplay.vue', () => {
	'use strict'
	let wrapper: any

	const dispatchStub = vi.fn()
	const commitStub = vi.fn()
	beforeAll(() => {
		wrapper = shallowMount(App, {
			global: {
				mocks: {
					$store: {
						state: {
							items: {
								playingItem: undefined,
							},
							app: {
								error: undefined,
							},
						},
						dispatch: dispatchStub,
						commit: commitStub,
					},
				},
				stubs: {
					RouterView: true,
				},
			},
		})
	})

	beforeEach(() => {
		dispatchStub.mockReset()
		commitStub.mockReset()
	})

	it('should send SET_PLAYING_ITEM with undefined to stop playback', () => {
		wrapper.vm.stopPlaying()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_PLAYING_ITEM, undefined)
	})

	it('should stop all video elements in page when playing video', () => {
		const pauseStub = vi.fn()
		document.getElementsByTagName = vi.fn().mockReturnValue([{ pause: pauseStub }])

		wrapper.vm.stopVideo()

		expect(pauseStub).toBeCalled()
	})

	it('should remove app state error with commit and undefined', () => {
		wrapper.vm.removeError()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_ERROR, undefined)
	})

	it('should reload when token error is shown', () => {
		const reloadSpy = vi.spyOn(wrapper.vm, 'reloadPage').mockImplementation(() => {})
		const error = new Error('Token expired or app not enabled! Reload the page!')

		wrapper.vm.$options.watch['app.error'].handler.call(wrapper.vm, error)

		expect(reloadSpy).toHaveBeenCalled()
	})
})
