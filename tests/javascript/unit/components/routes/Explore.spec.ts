import axios from '@nextcloud/axios'
import { shallowMount, createLocalVue } from '@vue/test-utils'

import * as router from '@nextcloud/router'

import Explore from '../../../../../src/components/routes/Explore.vue'

jest.mock('@nextcloud/axios')

describe('Explore.vue', () => {
	'use strict'
	const localVue = createLocalVue()

	it('should initialize without showing AddFeed Component', () => {
		(axios as any).get.mockResolvedValue({ data: { } });
		(router as any).generateUrl = jest.fn().mockReturnValue('')

		const wrapper = shallowMount(Explore, {
			localVue,
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
