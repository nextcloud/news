import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils'

import App from '../../../../src/App.vue'
import { MUTATIONS } from '../../../../src/store'

describe('FeedItemDisplay.vue', () => {
	'use strict'
	const localVue = createLocalVue()
	let wrapper: Wrapper<App>

	const dispatchStub = jest.fn()
	const commitStub = jest.fn()
	beforeAll(() => {
		wrapper = shallowMount(App, {
			localVue,
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
		const pauseStub = jest.fn()
		document.getElementsByTagName = jest.fn().mockReturnValue([{ pause: pauseStub }]);

		(wrapper.vm as any).stopVideo()

		expect(pauseStub).toBeCalled()
	})

	it('should remove app state error with commit and undefined', () => {
		(wrapper.vm as any).removeError()

		expect(commitStub).toBeCalledWith(MUTATIONS.SET_ERROR, undefined)
	})
})
