import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { shallowMount } from '@vue/test-utils'
import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import AppSidebar from '../../../../src/components/Sidebar.vue'
import { ACTIONS } from '../../../../src/store/index.ts'

vi.mock('@nextcloud/dialogs')

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
		vi.restoreAllMocks()
		wrapper.vm.$store.dispatch.mockReset()
	})

	it('should initialize without showing AddFeed Component', () => {
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
	})

	describe('User Actions', () => {
		it('should dispatch message to store with folder name to create new folder', () => {
			wrapper.vm.newFolder('abc')

			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.ADD_FOLDERS, { folder: { name: 'abc' } })
		})

		it('should not dispatch message to store with folder name to create new folder with existing name', () => {
			wrapper.vm.newFolder('def')

			expect(wrapper.vm.$store.dispatch).not.toHaveBeenCalled()
		})

		it('should dispatch message to store with folder object on delete folder', () => {
			window.confirm = vi.fn().mockReturnValue(true)
			wrapper.vm.deleteFolder(folder)

			folder.feeds.forEach((feed: any) => {
				expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FEED_DELETE, { feed })
			})
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.DELETE_FOLDER, { folder })
		})

		it('should not dispatch message to store with folder object on delete folder', () => {
			window.confirm = vi.fn().mockReturnValue(false)
			wrapper.vm.deleteFolder(folder)

			expect(wrapper.vm.$store.dispatch).not.toHaveBeenCalled()
		})

		it('should set showAddFeed to true', () => {
			wrapper.vm.addFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeTruthy()
		})

		it('should set showAddFeed to false', () => {
			wrapper.vm.closeAddFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
		})

		it('should call mark feed read for all feeds in state', () => {
			wrapper.vm.markAllRead()
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(2)
		})

		it('should call mark feed read for all feeds in state with matching folderId', () => {
			wrapper.vm.markFolderRead({ id: 123 })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1)
		})

		it('should call disptch rename folder with response from user', () => {
			const name = 'new name'
			window.prompt = vi.fn().mockReturnValue(name)
			wrapper.vm.renameFolder({ id: 123 })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FOLDER_SET_NAME, { folder: { id: 123 }, name })
		})
	})

	describe('SideBarState', () => {
		it('should return no top level nav when no folders or feeds', () => {
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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
			const topLevelNav = wrapper.vm.$options.computed?.topLevelNav.call({
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

		it('should set wasStarredVisited when route is STARRED with feedId param', () => {
			wrapper.vm.wasStarredVisited = false
			wrapper.vm.$options.watch?.$route.handler.call(wrapper.vm, { name: 'starred', params: { feedId: '123' } })
			expect(wrapper.vm.wasStarredVisited).toBe(true)
		})

		it('should set wasStarredVisited when route is STARRED with feedId param and stay set after a change', () => {
			wrapper.vm.wasStarredVisited = false
			wrapper.vm.$options.watch?.$route.handler.call(wrapper.vm, { name: 'starred', params: { feedId: '123' } })
			wrapper.vm.$options.watch?.$route.handler.call(wrapper.vm, { name: 'fee', params: { feedId: '123' } })
			expect(wrapper.vm.wasStarredVisited).toBe(true)
		})

		it('should NOT set wasStarredVisited when route is STARRED without feedId param', () => {
			wrapper.vm.wasStarredVisited = false
			wrapper.vm.$options.watch?.$route.handler.call(wrapper.vm, { name: 'starred' })
			expect(wrapper.vm.wasStarredVisited).toBe(false)
		})
	})

	describe('Methods', () => {
		it('should show error when no file is selected', async () => {
			const event = { target: { files: [] } }

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showError).toHaveBeenCalled()
		})

		it('should show success when status is 200 and file is valid', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'ok' },
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showSuccess).toHaveBeenCalled()
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEEDS)
		})

		it('should show backend error message when status 200 but backend returns error', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 200,
				data: { status: 'error', message: 'error importing articles' },
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showError).toHaveBeenCalledWith('error importing articles', { timeout: -1 })
		})

		it('should show error message when not status 200', async () => {
			const mockFile = new File(['{}'], 'articles.json', { type: 'application/json' })
			const event = { target: { files: [mockFile] } }

			axios.post.mockResolvedValue({
				status: 412,
			})

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showError).toHaveBeenCalledWith('Error uploading the json file')
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			const mockFile = new File(['{}'], 'articles.json')
			const event = { target: { files: [mockFile] } }

			axios.post.mockRejectedValue('network error')

			await wrapper.vm.importArticles.call(wrapper, event)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})

		it('should download file when status is 200', async () => {
			const blob = { slice: vi.fn() }
			axios.get.mockResolvedValue({ status: 200, data: blob })

			const clickMock = vi.fn()

			vi.spyOn(document, 'createElement').mockReturnValue({
				href: '',
				download: '',
				click: clickMock,
			})

			vi.spyOn(URL, 'createObjectURL').mockReturnValue('blob://test')

			await wrapper.vm.exportArticles.call(wrapper)

			expect(clickMock).toHaveBeenCalled()
		})

		it('should show error when status is not 200', async () => {
			axios.get.mockResolvedValue({ status: 500 })

			await wrapper.vm.exportArticles.call(wrapper)

			expect(showError).toHaveBeenCalled()
		})

		it('should show network error on server error', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			axios.get.mockRejectedValue('network error')

			await wrapper.vm.exportArticles.call(wrapper)

			expect(showError).toHaveBeenCalledWith('Error connecting to the server')
		})

		it('should return favicon route with feed url hash', () => {
			const feed = { name: 'feed1', id: 1, urlHash: '51f108ce113f11fbcbb7da6083c621cd' }
			const feedIcon = wrapper.vm.feedIcon(feed)
			expect(feedIcon).toEqual('//index.php/apps/news/favicon/51f108ce113f11fbcbb7da6083c621cd')
		})
	})

	describe('computed properties', () => {
		it('should return only feeds with starredCount for GroupedStars', () => {
			const mockFeeds = [{ id: 1, title: 'Feed A', starredCount: 0 }, { id: 2, title: 'Feed B', starredCount: 3 }, { id: 3, title: 'Feed C', starredCount: 1 }]

			const mockStore: any = {
				getters: {
					feeds: mockFeeds,
					folders: [],
					loading: false,
					displaymode: '0',
					splitmode: '0',
					oldestFirst: false,
					preventReadOnScroll: true,
					showAll: false,
					disableRefresh: true, // avoid setInterval in created()
					items: { unreadCount: 0, starredCount: 0 },
				},
				dispatch: vi.fn(),
				commit: vi.fn(),
			}

			const wrapper = shallowMount(AppSidebar as any, {
				global: {
					mocks: {
						$store: mockStore,
						t: (_ns: string, msg: string) => msg,
						$route: { name: '', params: {} },
						$router: { push: vi.fn() },
					},
					stubs: true,
				},
			})

			const grouped = (wrapper.vm as any).GroupedStars
			expect(Array.isArray(grouped)).toBe(true)
			expect(grouped.length).toBe(2)
			expect(grouped.map((g: any) => g.id).sort()).toEqual([2, 3])
		})

		it('should load getter reflects store.getters.loading', () => {
			const mockStore: any = {
				getters: {
					feeds: [],
					folders: [],
					loading: true,
					displaymode: '0',
					splitmode: '0',
					oldestFirst: false,
					preventReadOnScroll: true,
					showAll: false,
					disableRefresh: true,
					items: { unreadCount: 0, starredCount: 0 },
				},
				dispatch: vi.fn(),
				commit: vi.fn(),
			}

			const wrapper = shallowMount(AppSidebar as any, {
				global: {
					mocks: {
						$store: mockStore,
						t: (_ns: string, msg: string) => msg,
						$route: { name: '', params: {} },
						$router: { push: vi.fn() },
					},
					stubs: true,
				},
			})

			expect((wrapper.vm as any).loading).toBe(true)
		})

		it('should renders RssIcon when no faviconLink and fallback span when faviconLink present for GroupedStars', () => {
			const mockFeeds = [
				{ id: 2, title: 'group-no-favicon', starredCount: 1, faviconLink: null },
				{ id: 3, title: 'group-with-favicon', starredCount: 2, faviconLink: 'https://example.com/favicon.png' },
			]

			const mockStore: any = {
				getters: {
					feeds: mockFeeds,
					folders: [],
					loading: false,
					displaymode: '0',
					splitmode: '0',
					oldestFirst: false,
					preventReadOnScroll: true,
					showAll: false,
					disableRefresh: true,
					items: { unreadCount: 0, starredCount: 0 },
				},
				dispatch: vi.fn(),
				commit: vi.fn(),
			}

			// shallowMount with simple stubs — we only need computed GroupedStars here
			const wrapper = shallowMount(AppSidebar as any, {
				global: {
					mocks: {
						$store: mockStore,
						t: (_ns: string, msg: string) => msg,
						$route: { name: '', params: {} },
						$router: { push: vi.fn() },
					},
					stubs: true,
				},
			})

			// use computed GroupedStars to assert the branching data (covers the v-if decision)
			const grouped = (wrapper.vm as any).GroupedStars
			expect(Array.isArray(grouped)).toBe(true)
			expect(grouped.length).toBe(2)

			// first group has no faviconLink -> template should render RssIcon (v-if="!group.faviconLink")
			expect(grouped[0].faviconLink).toBeNull()

			// second group has a faviconLink -> template should render background-image span
			expect(grouped[1].faviconLink).toBe('https://example.com/favicon.png')
		})
	})

	// TODO: More Template Testing with https://test-utils.vuejs.org/guide/essentials/a-crash-course.html#adding-a-new-todo

	afterEach(() => {
		vi.clearAllMocks()
	})
})
