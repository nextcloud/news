import { ACTIONS } from '@/store';
import { Wrapper, shallowMount, createLocalVue } from '@vue/test-utils'

import AppSidebar from 'Components/Sidebar.vue'

describe('Sidebar.vue', () => {
	'use strict'

	let wrapper: Wrapper<AppSidebar>;

	beforeAll(() => {
		const localVue = createLocalVue()
		wrapper = shallowMount(AppSidebar, { 
			localVue,
			mocks: { 
				$store: { 
					state: { 
						feeds: [], 
						folders: [] 
					}
				}
			}
		})
		wrapper.vm.$store.dispatch = jest.fn();
	})

	it('should initialize without showing AddFeed Component', () => {
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	});

	it('should dispatch message to store with folder name to create new folder', () => {
		(wrapper.vm as any).newFolder('abc')
	
		expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.ADD_FOLDERS,  { folder: { name: 'abc'} })
	});

	it('should dispatch message to store with folder object on delete folder', () => {
		const folder = {};
		(wrapper.vm as any).deleteFolder(folder)

		expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.DELETE_FOLDER, { folder })
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

	describe('SideBar State', () => {
		// it('should return top level nav (folders and feeds without folders)', () => {
		// 	const navItems = (wrapper.vm.$options?.computed?.topLevelNav as any)({ feeds: [], folders: [] });

		// 	console.log(navItems)
		// })
	})
})
