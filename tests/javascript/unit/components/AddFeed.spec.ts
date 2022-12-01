import { shallowMount, createLocalVue } from '@vue/test-utils'

import AddFeed from '../../../../src/components/AddFeed.vue'

describe('AddFeed.vue', () => {
	'use strict'

	it('should initialize without showing createNewFolder', () => {
		const localVue = createLocalVue()
		const wrapper = shallowMount(AddFeed, {
			localVue,
			mocks: {
				$store: {
					state: {
						folders: [],
					},
				},
			},
		})

		expect(wrapper.vm.$data.createNewFolder).toBeFalsy()
	})
})
