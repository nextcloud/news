import { shallowMount } from '@vue/test-utils'
import { store, localVue } from '../setupStore'

import AddFeed from 'Components/AddFeed.vue'

describe('AddFeed.vue', () => {
	'use strict'

	it('should initialize without showing createNewFolder', () => {
		const wrapper = shallowMount(AddFeed, { localVue, store })

		expect(wrapper.vm.$data.createNewFolder).toBeFalsy
	});
});