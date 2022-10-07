import AddFeed from 'Components/AddFeed.vue'

import { store, localVue } from '../setupStore'

import { shallowMount } from '@vue/test-utils'

describe('AddFeed.vue', () => {
	'use strict'

	it('should initialize without showing createNewFolder', () => {
		const wrapper = shallowMount(AddFeed, { localVue, store })

		expect(wrapper.vm.$data.createNewFolder).toBeFalsy
	});
});