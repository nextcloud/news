import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { shallowMount } from '@vue/test-utils'
import { afterAll, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import AdminSettings from '../../../../src/components/AdminSettings.vue'

import 'regenerator-runtime/runtime' // NOTE: Required for testing password-confirmation?

vi.mock('@nextcloud/axios')
vi.mock('@nextcloud/initial-state')
vi.mock('@nextcloud/router')
vi.mock('@nextcloud/dialogs')
vi.mock('@nextcloud/password-confirmation', () => ({
	confirmPassword: vi.fn(),
	password_policy: {},
}))
vi.mock('@nextcloud/vue', () => ({
	NcAppContent: {},
}))

describe('AdminSettings.vue', () => {
	'use strict'

	let wrapper: any

	beforeAll(() => {
		(loadState as any).mockReturnValue('')
		wrapper = shallowMount(AdminSettings, { })
	})

	beforeEach(() => {
		vi.useFakeTimers()
	})

	it('should initialize and fetch settings from state', () => {
		expect(loadState).toBeCalledTimes(9)
	})

	it('should send post with updated settings', async () => {
		vi.spyOn(axios, 'post').mockResolvedValue({ data: {} });
		(wrapper.vm as any).handleResponse = vi.fn()

		await wrapper.vm.$options?.methods?.update.call(wrapper.vm, 'key', 'val')

		expect(axios.post).toBeCalledTimes(1)
	})

	it('should handle bad response', () => {
		(showError as any).mockClear()
		console.error = vi.fn()
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			error: true,
			errorMessage: 'FAIL',
		})

		expect(showError).toBeCalledTimes(1)
	})

	it('should handle success response', () => {
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			status: 'ok',
		});
		(global as any).t = vi.fn()
		vi.runAllTimers()

		expect(showSuccess).toBeCalledTimes(1)
	})

	afterAll(() => {
		vi.clearAllMocks()
		vi.useRealTimers()
	})
})
