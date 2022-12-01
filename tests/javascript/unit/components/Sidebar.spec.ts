import { ACTIONS } from '../../../../src/store'
import { Wrapper, shallowMount, createLocalVue } from '@vue/test-utils'

import AppSidebar from '../../../../src/components/Sidebar.vue'

describe('Sidebar.vue', () => {
	'use strict'

	let wrapper: Wrapper<AppSidebar>

	beforeAll(() => {
		const localVue = createLocalVue()
		wrapper = shallowMount(AppSidebar, {
			localVue,
			mocks: {
				$store: {
					state: {
						feeds: [],
						folders: [],
					},
					dispatch: jest.fn(),
				},
			},
		})
		// wrapper.vm.$store.
	})

	it('should initialize without showing AddFeed Component', () => {
		expect((wrapper.vm as any).$data.showAddFeed).toBeFalsy()
	})

	it('should dispatch message to store with folder name to create new folder', () => {
		(wrapper.vm as any).newFolder('abc')

		expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.ADD_FOLDERS, { folder: { name: 'abc' } })
	})

	it('should dispatch message to store with folder object on delete folder', () => {
		const folder = {};
		(wrapper.vm as any).deleteFolder(folder)

		expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.DELETE_FOLDER, { folder })
	})

	it('should set showAddFeed to true', () => {
		(wrapper.vm as any).showShowAddFeed()
		expect(wrapper.vm.$data.showAddFeed).toBeTruthy()
	})

	it('should set showAddFeed to false', () => {
		(wrapper.vm as any).closeShowAddFeed()
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
	})

	// TODO: A couple more tests here
	it('should return top level nav (folders and feeds without folders)', () => {
		const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
			$store: {
				getters: {
					feeds: [],
					folders: [],
				},
			},
		})

		expect(topLevelNav).toEqual([])
	})

	// TODO: More Template Testing with https://test-utils.vuejs.org/guide/essentials/a-crash-course.html#adding-a-new-todo

	afterEach(() => {
		jest.clearAllMocks()
	})

	describe('SideBar State', () => {
		// TODO
	})
})
