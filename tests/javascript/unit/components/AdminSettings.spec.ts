import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { shallowMount } from '@vue/test-utils'
import { afterAll, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import AdminSettings from '../../../../src/components/AdminSettings.vue'

import 'regenerator-runtime/runtime' // NOTE: Required for testing password-confirmation?

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
		loadState.mockReturnValue('')
		wrapper = shallowMount(AdminSettings, {
			data() {
				return {
					updateInterval: 3600,
				}
			},
		})
	})

	beforeEach(() => {
		vi.useFakeTimers()
	})

	it('should initialize and fetch settings from state', () => {
		expect(loadState).toBeCalledTimes(10)
	})

	it('returns true when lastCron is too old', async () => {
		const now = 1000000000
		vi.setSystemTime(now * 1000)
		wrapper.vm.lastCron = now - 8200

		expect(wrapper.vm.oldExecution).toBe(true)
	})

	it('returns false when lastCron within range', async () => {
		const now = 1000000000
		vi.setSystemTime(now * 1000)
		wrapper.vm.lastCron = now - 8000

		expect(wrapper.vm.oldExecution).toBe(false)
	})

	it('returns true when lastLogoPurge is more than 7 days ago', () => {
		const now = 1000000000
		vi.setSystemTime(now * 1000)
		wrapper.vm.lastLogoPurge = now - 605800

		expect(wrapper.vm.oldLastLogoPurge).toBe(true)
	})

	it('returns false when lastLogoPurge is less than 7 days ago', () => {
		const now = 1000000000
		vi.setSystemTime(now * 1000)
		wrapper.vm.lastLogoPurge = now - 604800

		expect(wrapper.vm.oldLastLogoPurge).toBe(false)
	})

	it('should send post with updated settings', async () => {
		vi.spyOn(axios, 'post').mockResolvedValue({ data: {} })
		wrapper.vm.handleResponse = vi.fn()

		await wrapper.vm.$options?.methods?.update.call(wrapper.vm, 'key', 'val')

		expect(axios.post).toBeCalledTimes(1)
	})

	it('should handle bad response', () => {
		showError.mockClear()
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
		})
		global.t = vi.fn()
		vi.runAllTimers()

		expect(showSuccess).toBeCalledTimes(1)
	})

	afterAll(() => {
		vi.clearAllMocks()
		vi.useRealTimers()
	})
})
