import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import AppSidebar from '../../../../src/components/Sidebar.vue'
import { ACTIONS } from '../../../../src/store'

describe('Sidebar.vue', () => {
	'use strict'

	let wrapper: any

	const feeds = [{
		id: 1, title: 'first',
	}, {
		id: 2, title: 'second', folderId: 123,
	}]

	const folder = {
		id: 123,
		name: 'abc',
		feeds: [{ id: 1, title: 'first', folderId: 123 }],
	}

	const folders = [{
		id: 456,
		name: 'def',
	}]

	beforeAll(() => {
		wrapper = shallowMount(AppSidebar, {
			global: {
				mocks: {
					$route: {
						query: {
							subscribe_to: undefined,
						},
					},
					$store: {
						state: {
							feeds,
							folders: [],
						},
						getters: {
							feeds,
							folders,
							showAll: () => { return true },
						},
						dispatch: vi.fn(),
					},
				},
			},
		})
	})

	beforeEach(() => {
		(wrapper.vm as any).$store.dispatch.mockReset()
	})

	it('should initialize without showing AddFeed Component', () => {
		expect((wrapper.vm as any).$data.showAddFeed).toBeFalsy()
	})

	describe('User Actions', () => {
		it('should dispatch message to store with folder name to create new folder', () => {
			(wrapper.vm as any).newFolder('abc')

			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.ADD_FOLDERS, { folder: { name: 'abc' } })
		})

		it('should not dispatch message to store with folder name to create new folder with existing name', () => {
			(wrapper.vm as any).newFolder('def')

			expect((wrapper.vm as any).$store.dispatch).not.toHaveBeenCalled()
		})

		it('should dispatch message to store with folder object on delete folder', () => {
			window.confirm = vi.fn().mockReturnValue(true);
			(wrapper.vm as any).deleteFolder(folder)

			folder.feeds.forEach((feed: any) => {
				expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed })
			})
			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.DELETE_FOLDER, { folder })
		})

		it('should not dispatch message to store with folder object on delete folder', () => {
			window.confirm = vi.fn().mockReturnValue(false);
			(wrapper.vm as any).deleteFolder(folder)

			expect((wrapper.vm as any).$store.dispatch).not.toHaveBeenCalled()
		})

		it('should set showAddFeed to true', () => {
			(wrapper.vm as any).addFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeTruthy()
		})

		it('should set showAddFeed to false', () => {
			(wrapper.vm as any).closeAddFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
		})

		it('should call mark feed read for all feeds in state', () => {
			(wrapper.vm as any).markAllRead()
			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledTimes(2)
		})

		it('should call mark feed read for all feeds in state with matching folderId', () => {
			(wrapper.vm as any).markFolderRead({ id: 123 })
			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledTimes(1)
		})

		it('should call disptch rename folder with response from user', () => {
			const name = 'new name'
			window.prompt = vi.fn().mockReturnValue(name);
			(wrapper.vm as any).renameFolder({ id: 123 })
			expect((wrapper.vm as any).$store.dispatch).toHaveBeenCalledWith(ACTIONS.FOLDER_SET_NAME, { folder: { id: 123 }, name })
		})
	})

	describe('SideBarState', () => {
		it('should return no top level nav when no folders or feeds', () => {
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds: [],
						folders: [],
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav).toEqual([])
		})

		it('should return top level nav with 1 feed', () => {
			const feeds: any[] = [{ name: 'feed1', id: 1 }]
			const folders: any[] = []
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds,
						folders,
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav).toEqual([feeds[0]])
		})

		it('should return top level nav with 1 folder (with feeds)', () => {
			const feeds: any[] = [{ name: 'feed2', id: 2, folderId: 123 }]
			const folders: any[] = [{ name: 'abc', id: 123 }]
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds,
						folders,
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav).toEqual(folders)
		})

		it('should return top level nav with 1 folder (without feed)', () => {
			const feeds: any[] = [{ name: 'feed1', id: 1 }]
			const folders: any[] = [{ name: 'abc', id: 123 }]
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds,
						folders,
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav).toEqual([feeds[0], ...folders])
		})

		it('should return top level nav with feeds and folders', () => {
			const feeds: any[] = [{ name: 'feed1', id: 1 }, { name: 'feed2', id: 2, folderId: 123 }]
			const folders: any[] = [{ name: 'abc', id: 123 }, { name: 'xyz', id: 234 }]
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds,
						folders,
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav).toEqual([feeds[0], ...folders])
		})

		it('should set pinned feeds at beginning top level nav with feeds and folders', () => {
			const feeds: any[] = [{ name: 'feed1', id: 1 }, { name: 'feed2', id: 2, folderId: 123 }, { name: 'feed3', id: 3, pinned: true }]
			const folders: any[] = [{ name: 'abc', id: 123 }, { name: 'xyz', id: 234 }]
			const topLevelNav = (wrapper.vm.$options.computed?.topLevelNav as any).call({
				$store: {
					getters: {
						feeds,
						folders,
						showAll: () => { return true },
					},
				},
			})

			expect(topLevelNav[0].name).toEqual('feed3')
		})
	})

	// TODO: More Template Testing with https://test-utils.vuejs.org/guide/essentials/a-crash-course.html#adding-a-new-todo

	afterEach(() => {
		vi.clearAllMocks()
	})
})
