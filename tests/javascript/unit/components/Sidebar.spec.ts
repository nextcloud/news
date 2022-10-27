import { Wrapper, shallowMount } from '@vue/test-utils'
import { store, localVue } from '../setupStore'

import AppSidebar from 'Components/Sidebar.vue'

describe('Sidebar.vue', () => {
	'use strict'

	let wrapper: Wrapper<AppSidebar>;

	beforeAll(() => {
		wrapper = shallowMount(AppSidebar, { localVue, store })
		wrapper.vm.$store.dispatch = jest.fn();
	})

	it('should initialize without showing AddFeed Component', () => {
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	});

	it('should dispatch message to store with folder name to create new folder', () => {
		(wrapper.vm as any).newFolder('abc')
	
		expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('addFolder',  { folder: { name: 'abc'} })
	});

	it('should dispatch message to store with folder object on delete folder', () => {
		const folder = {};
		(wrapper.vm as any).deleteFolder(folder)

		expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith('deleteFolder', { folder })
	})

	it('should set showAddFeed to true', () => {
		(wrapper.vm as any).showShowAddFeed()
		expect(wrapper.vm.$data.showAddFeed).toBeTruthy
	})

	it('should set showAddFeed to false', () => {
		(wrapper.vm as any).closeShowAddFeed()
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	})

	afterEach(() => {
		jest.clearAllMocks();
	});
})
