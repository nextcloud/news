import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { shallowMount } from '@vue/test-utils'
import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import AppSidebar from '../../../../src/components/Sidebar.vue'
import { ACTIONS } from '../../../../src/store/index.ts'

vi.mock('@nextcloud/dialogs')

describe('Sidebar.vue', () => {
	'use strict'

	let wrapper: any
	let commitMock
	let gettersMock
	let routeMock

	const feeds = [{
		id: 1,
		title: 'first',
	}, {
		id: 2,
		title: 'second',
		folderId: 123,
	}, {
		id: 3,
		title: 'three',
	}]

	const folder = {
		id: 123,
		name: 'abc',
		feeds: [{ id: 1, title: 'first', folderId: 123 }],
	}

	const folders = [{
		id: 123,
		name: 'abc',
		opened: true,
		feeds: [feeds[1]],
	}, {
		id: 456,
		name: 'def',
	}, {
		id: 789,
		name: 'ghi',
	}]

	beforeEach(() => {
		vi.restoreAllMocks()
		vi.useFakeTimers()
		Element.prototype.scrollIntoView = vi.fn()
		gettersMock = {
			feeds,
			folders,
			showAll: true,
			starredOpenState: false,
			disableRefresh: false,
		}
		commitMock = vi.fn((type, payload) => {
			if (type === 'starredOpenState') {
				gettersMock.starredOpenState = payload.value
			}
		})
		routeMock = {
			query: {
				subscribe_to: undefined,
			},
			name: 'unread',
			params: { feedId: undefined, folderId: undefined },
		}
		wrapper = shallowMount(AppSidebar, {
			global: {
				mocks: {
					$route: routeMock,
					$router: {
						push: (route) => {
							routeMock.name = route.name
							routeMock.params = route.params
						},
					},
					$store: {
						state: {
							feeds,
							folders: [],
						},
						getters: gettersMock,
						commit: commitMock,
						dispatch: vi.fn(),
					},
				},
			},
		})
	})

	it('should initialize without showing AddFeed Component', () => {
		expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
	})

	describe('User Actions', () => {
		it('should dispatch message to store with folder name to create new folder', () => {
			wrapper.vm.newFolder('xyz')

			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.ADD_FOLDERS, { folder: { name: 'xyz' } })
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

		it('should call mark feed read for all feeds in state', () => {
			wrapper.vm.markAllRead()
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(3)
		})

		it('should call mark feed read for all feeds in state with matching folderId', () => {
			wrapper.vm.markFolderRead({ id: 123 })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledTimes(1)
		})

		it('should call dispatch rename folder with response from user', () => {
			const name = 'new name'
			window.prompt = vi.fn().mockReturnValue(name)
			wrapper.vm.renameFolder({ id: 123 })
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FOLDER_SET_NAME, { folder: { id: 123 }, name })
		})

		it('should toggle starred open state to open', async () => {
			gettersMock.starredOpenState = false
			await wrapper.vm.toggleStarredOpenState.call(wrapper)
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('starredOpenState', { value: true })
			expect(wrapper.vm.isStarredOpen).toBeTruthy()
		})

		it('should toggle starred open state to false', async () => {
			gettersMock.starredOpenState = true
			await wrapper.vm.toggleStarredOpenState.call(wrapper)
			expect(wrapper.vm.$store.commit).toHaveBeenCalledWith('starredOpenState', { value: false })
			expect(wrapper.vm.isStarredOpen).toBeFalsy()
		})

		it('should show server error when saving starred open state failed', async () => {
			vi.spyOn(console, 'error').mockImplementation(() => {})
			axios.post.mockRejectedValue('network error')
			await wrapper.vm.toggleStarredOpenState.call(wrapper)
			expect(showError).toHaveBeenCalledWith('Unable to save starred open state')
		})

		it('should toggle folder open state', () => {
			const folder = { name: 'folder1', id: 123, opened: true }

			wrapper.vm.toggleFolderState(folder)
			expect(folder.opened).toBeFalsy()
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FOLDER_OPEN_STATE, { folder: { name: 'folder1', id: 123, opened: false } })

			wrapper.vm.toggleFolderState(folder)
			expect(folder.opened).toBeTruthy()
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FOLDER_OPEN_STATE, { folder: { name: 'folder1', id: 123, opened: true } })
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
						showAll: () => { return false },
					},
				},
			})

			expect(topLevelNav[0].name).toEqual('feed3')
		})
	})

	describe('Methods', () => {
		it('should enable automatic refresh', () => {
			const setIntervalMock = vi.spyOn(global, 'setInterval')
			gettersMock.disableRefresh = false
			wrapper.vm.pollInterval = null
			wrapper.vm.enableAutoFetch()
			expect(setIntervalMock).toHaveBeenCalled()
			expect(wrapper.vm.pollInterval).not.toBeNull()
		})

		it('should not enable automatic refresh twice', () => {
			const setIntervalMock = vi.spyOn(global, 'setInterval')
			gettersMock.disableRefresh = false
			wrapper.vm.pollInterval = null
			wrapper.vm.enableAutoFetch()
			wrapper.vm.enableAutoFetch()
			expect(setIntervalMock).toHaveBeenCalledTimes(1)
		})

		it('should disable automatic refresh', () => {
			const clearIntervalMock = vi.spyOn(global, 'clearInterval')
			gettersMock.disableRefresh = true
			wrapper.vm.disableAutoFetch()
			expect(clearIntervalMock).toHaveBeenCalled()
			expect(wrapper.vm.pollInterval).toBeNull()
		})

		it('should automatic refresh feeds after 60 seconds', () => {
			gettersMock.disableRefresh = false
			wrapper.vm.enableAutoFetch()
			vi.advanceTimersByTime(60000)
			expect(wrapper.vm.$store.dispatch).toHaveBeenCalledWith(ACTIONS.FETCH_FEEDS)
		})

		it('should not automatic refresh feeds after 60 seconds', () => {
			gettersMock.disableRefresh = true
			wrapper.vm.disableAutoFetch()
			vi.advanceTimersByTime(60000)
			expect(wrapper.vm.$store.dispatch).not.toHaveBeenCalledWith(ACTIONS.FETCH_FEEDS)
		})

		it('should set showAddFeed to true', () => {
			wrapper.vm.addFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeTruthy()
		})

		it('should set showAddFeed to false', () => {
			wrapper.vm.closeAddFeed()
			expect(wrapper.vm.$data.showAddFeed).toBeFalsy()
		})

		it('should set showMoveFeed to true', () => {
			wrapper.vm.openMoveFeed()
			expect(wrapper.vm.$data.showMoveFeed).toBeTruthy()
		})

		it('should set showMoveFeed to false', () => {
			wrapper.vm.closeMoveFeed()
			expect(wrapper.vm.$data.showMoveFeed).toBeFalsy()
		})

		it('should set openFeedSettings to true', () => {
			wrapper.vm.openFeedSettings()
			expect(wrapper.vm.$data.showFeedSettings).toBeTruthy()
		})

		it('should set closeFeedSettings to false', () => {
			wrapper.vm.closeFeedSettings()
			expect(wrapper.vm.$data.showFeedSettings).toBeFalsy()
		})

		it('should set openSettings to true', () => {
			wrapper.vm.openSettings()
			expect(wrapper.vm.$data.showSettings).toBeTruthy()
		})

		it('should set closeSettings to false', () => {
			wrapper.vm.closeSettings()
			expect(wrapper.vm.$data.showSettings).toBeFalsy()
		})

		it('should return true if item is folder', () => {
			const folder = { name: 'folder1', id: 123 }
			const isFolder = wrapper.vm.isFolder(folder)
			expect(isFolder).toBeTruthy()
		})

		it('should return true if feed is newly added', () => {
			gettersMock.showAll = false

			const added = 1768000576
			const lastModified = added * 1000000
			const feed = { title: 'feed1', id: 1, added, lastModified }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if feed is active and showAll is unset', () => {
			gettersMock.showAll = false
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 1 } })

			const feed = { title: 'feed1', id: 1 }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if feed has unread items and showAll is unset', () => {
			gettersMock.showAll = false

			const feed = { title: 'feed1', id: 1, unreadCount: 5 }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if feed error count is greater eight and showAll is unset', () => {
			gettersMock.showAll = false

			const feed = { title: 'feed1', id: 1, updateErrorCount: 9 }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if feed has no unread items and no errors and showAll is set', () => {
			gettersMock.showAll = true

			const feed = { title: 'feed1', id: 1, unreadCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(true)
			expect(showItem).toBeTruthy()
		})

		it('should return false if feed has no unread items and no errors and showAll is not set', () => {
			gettersMock.showAll = false

			const feed = { title: 'feed1', id: 1, unreadCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(feed)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeFalsy()
		})

		it('should return true if folder has active items and showAll is unset', () => {
			gettersMock.showAll = false
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 1 } })

			const feed = { title: 'feed1', id: 1, folderId: 123 }
			const folder = { name: 'folder1', id: 123, feeds: [feed] }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder is active and showAll is unset', () => {
			gettersMock.showAll = false
			wrapper.vm.$router.push({ name: 'folder', params: { folderId: 123 } })

			const feed = { title: 'feed1', id: 1, folderId: 123 }
			const folder = { name: 'folder1', id: 123, feeds: [feed] }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder has unread items and showAll is unset', () => {
			gettersMock.showAll = false

			const folder = { name: 'folder1', id: 123, feeds, feedCount: 5 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder error count is greater eight and showAll is unset', () => {
			gettersMock.showAll = false

			const folder = { name: 'folder1', id: 123, feeds, updateErrorCount: 9 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder has no unread items and no errors and showAll is set', () => {
			gettersMock.showAll = true

			const folder = { name: 'folder1', id: 123, feeds, feedCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(true)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder has no feeds and no errors and showAll is not set', () => {
			gettersMock.showAll = false

			const folder = { name: 'folder1', id: 123, feeds: [], feedCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return true if folder has newly added feeds and no errors and showAll is not set', () => {
			gettersMock.showAll = false

			const added = 1768000576
			const modified = added * 1000000
			const feed = { title: 'feed1', id: 1, folderId: 123, added, lastModified: modified }
			const folder = { name: 'folder1', id: 123, feeds: [feed], feedCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeTruthy()
		})

		it('should return false if folder has no unread items and no errors and showAll is not set', () => {
			gettersMock.showAll = false

			const folder = { name: 'folder1', id: 123, feeds, feedCount: 0, updateErrorCount: 0 }
			const showItem = wrapper.vm.showItem(folder)
			expect(wrapper.vm.showAll).toBe(false)
			expect(showItem).toBeFalsy()
		})

		// feeds are sorted alphabetically, so it is feedId 1 - 3 - 2
		it('should switch from active feed to prev feed', async () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 2 } })

			wrapper.vm.prevFeed()
			expect(routeMock.params.feedId).toEqual('3')
		})

		// feeds are sorted alphabetically, so it is feedId 1 - 3 - 2
		it('should switch from active feed to next feed', () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 1 } })

			wrapper.vm.nextFeed()
			expect(routeMock.params.feedId).toEqual('3')
		})

		// order feedId 1 - feedId 3 - folderId 123 - feedId 2 - folderId 456
		it('should switch from active folder to prev feed', async () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'folder', params: { folderId: 123 } })

			wrapper.vm.prevFeed()
			expect(routeMock.params.feedId).toEqual('3')
		})

		// order feedId 1 - feedId 3 - folderId 123 - feedId 2 - folderId 456
		it('should switch from active folder to next feed', () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'folder', params: { folderId: 123 } })

			wrapper.vm.nextFeed()
			expect(routeMock.params.feedId).toEqual('2')
		})

		// order folderId 123 - folderId 456 - folderId 789
		it('should switch from active folder to prev folder', async () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'folder', params: { folderId: 789 } })

			wrapper.vm.prevFolder()
			expect(routeMock.params.folderId).toEqual('456')
		})

		// order folderId 123 - folderId 456 - folderId 789
		it('should switch from active folder to next folder', () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'folder', params: { folderId: 123 } })

			wrapper.vm.nextFolder()
			expect(routeMock.params.folderId).toEqual('456')
		})

		// order feedId 1 - feedId 3 - folderId 123 - feedId 2 - folderId 456
		it('should switch from active feed to prev folder', async () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 2 } })

			wrapper.vm.prevFolder()
			expect(routeMock.params.folderId).toEqual('123')
		})

		// order feedId 1 - feedId 3 - folderId 123 - feedId 2 - folderId 456
		it('should switch from active feed to next folder', () => {
			gettersMock.showAll = true
			wrapper.vm.$router.push({ name: 'feed', params: { feedId: 2 } })

			wrapper.vm.nextFolder()
			expect(routeMock.params.folderId).toEqual('456')
		})

		it('should return favicon route with feed url hash', () => {
			const feed = { title: 'feed1', id: 1, urlHash: '51f108ce113f11fbcbb7da6083c621cd' }
			const feedIcon = wrapper.vm.feedIcon(feed)
			expect(feedIcon).toEqual('//index.php/apps/news/favicon/51f108ce113f11fbcbb7da6083c621cd')
		})
	})

	describe('computed properties', () => {
		it('should return only feeds with starredCount for GroupedStars', () => {
			const mockFeeds = [
				{ id: 1, title: 'Feed A', starredCount: 0 },
				{ id: 2, title: 'Feed B', starredCount: 3 },
				{ id: 3, title: 'Feed C', starredCount: 1 },
				{ id: 4, title: 'Feed D' },
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
	})

	describe('rendering of navigation item icons', () => {
		let wrapper
		beforeAll(() => {
			const mockFeeds = [
				{ id: 1, title: 'StarredFeed', starredCount: 3, urlHash: '51f108ce113f11fbcbb7da6083c621cd' },
				{ id: 2, title: 'FolderFeed', folderId: 1, urlHash: 'aef6257dd8d606def70d42e64f70095b' },
				{ id: 3, title: 'TopLevelFeed', urlHash: '6222ecb651666e00c83bb3ae27f4e714' },
			]

			const mockFolders = [
				{ id: 1, name: 'Folder', feeds: [] },
				{ id: 2, name: 'FolderWithError', feeds: [], updateErrorCount: 10 },
				{ id: 3, name: 'FolderWithFeed', feeds: [mockFeeds[1]] },
			]
			wrapper = shallowMount(AppSidebar, {
				global: {
					mocks: {
						$route: routeMock,
						$store: {
							getters: {
								feeds: mockFeeds,
								folders: mockFolders,
							},
						},
					},
					stubs: {
						'nc-app-navigation': {
							template: '<div><slot /><slot name="list" /></div>',
						},
						'nc-app-navigation-item': {
							template: '<div><slot /><slot name="icon" /></div>',
						},
					},
				},
			})
		})

		it('should render starred feed icon', () => {
			const starredDiv = wrapper.get('div[name="Starred"]')
			const feedDiv = starredDiv.get('div[name="StarredFeed"]')
			const span = feedDiv.get('span')

			const bgImage = span.element.style.backgroundImage
			const url = bgImage.slice(4, -1).replace(/["']/g, '')

			expect(url).toBe('//index.php/apps/news/favicon/51f108ce113f11fbcbb7da6083c621cd')
		})

		it('should render folder feed icon', () => {
			const folderDiv = wrapper.get('div[name="FolderWithFeed"]')
			const feedDiv = folderDiv.get('div[name="FolderFeed"]')
			const span = feedDiv.get('span')

			const bgImage = span.element.style.backgroundImage
			const url = bgImage.slice(4, -1).replace(/["']/g, '')

			expect(url).toBe('//index.php/apps/news/favicon/aef6257dd8d606def70d42e64f70095b')
		})

		it('should render top level feed icon', () => {
			const feedDiv = wrapper.get('div[name="TopLevelFeed"]')
			const span = feedDiv.get('span')

			const bgImage = span.element.style.backgroundImage
			const url = bgImage.slice(4, -1).replace(/["']/g, '')

			expect(url).toBe('//index.php/apps/news/favicon/6222ecb651666e00c83bb3ae27f4e714')
		})

		it('should render folder icon', () => {
			const folderDiv = wrapper.get('div[name="Folder"]')
			const folderIcon = folderDiv.get('folder-icon-stub')
			expect(folderIcon.exists()).toBe(true)
		})

		it('should render folder alert icon', () => {
			const folderDiv = wrapper.get('div[name="FolderWithError"]')
			const folderAlertIcon = folderDiv.get('folder-alert-icon-stub')
			expect(folderAlertIcon.exists()).toBe(true)
		})

		afterAll(() => {
			wrapper.unmount()
		})
	})

	// TODO: More Template Testing with https://test-utils.vuejs.org/guide/essentials/a-crash-course.html#adding-a-new-todo

	afterEach(() => {
		vi.clearAllMocks()
		vi.useRealTimers()
	})
})
