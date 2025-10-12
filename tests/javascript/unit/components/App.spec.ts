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
		(wrapper.vm as any).stopPlaying()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_PLAYING_ITEM, undefined)
	})

	it('should stop all video elements in page when playing video', () => {
		const pauseStub = vi.fn()
		document.getElementsByTagName = vi.fn().mockReturnValue([{ pause: pauseStub }]);

		(wrapper.vm as any).stopVideo()

		expect(pauseStub).toBeCalled()
	})

	it('should remove app state error with commit and undefined', () => {
		(wrapper.vm as any).removeError()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_ERROR, undefined)
	})
})
