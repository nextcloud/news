import AppSidebar from 'Components/Sidebar.vue'

import { store, localVue } from '../setupStore'

import { shallowMount } from '@vue/test-utils'

describe('Sidebar.vue', () => {
	'use strict'

	it('should initialize without showing AddFeed Component', () => {
		const wrapper = shallowMount(AppSidebar, { localVue, store })

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	});

	it('should dispatch message to store with folder name to create new folder');

	it('should dispatch message to store with folder object on delete folder')

	it('should set showAddFeed to true')

	it('should set showAddFeed to false')
})
