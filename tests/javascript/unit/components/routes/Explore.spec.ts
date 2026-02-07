import axios from '@nextcloud/axios'
import { shallowMount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import Explore from '../../../../../src/components/routes/Explore.vue'

vi.mock('@nextcloud/initial-state', () => ({
	loadState: vi.fn((app: string, key: string, fallback: any) => fallback),
}))

describe('Explore.vue', () => {
	'use strict'

	it('should initialize without showing AddFeed Component', () => {
		axios.get.mockResolvedValue({ data: { } })

		const wrapper = shallowMount(Explore, {
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
				},
			},
		})

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
	})
})
