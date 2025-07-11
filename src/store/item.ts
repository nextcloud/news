import type { ActionParams } from '../store/index.ts'
import type { Feed } from '../types/Feed.ts'
import type { FeedItem } from '../types/FeedItem.ts'

import { reactive } from 'vue'
import { ItemService } from '../dataservices/item.service'
import { FEED_ITEM_MUTATION_TYPES, FEED_MUTATION_TYPES } from '../types/MutationTypes.ts'
import { FEED_ACTION_TYPES } from './feed.ts'

export const FEED_ITEM_ACTION_TYPES = {
	FETCH_STARRED: 'FETCH_STARRED',
	FETCH_UNREAD: 'FETCH_UNREAD',
	MARK_READ: 'MARK_READ',
	MARK_UNREAD: 'MARK_UNREAD',
	STAR_ITEM: 'STAR_ITEM',
	UNSTAR_ITEM: 'UNSTAR_ITEM',
	FETCH_FEED_ITEMS: 'FETCH_FEED_ITEMS',
	FETCH_FOLDER_FEED_ITEMS: 'FETCH_FOLDER_FEED_ITEMS',
	FETCH_ITEMS: 'FETCH_ITEMS',
}

export type ItemState = {
	fetchingItems: { [key: string]: boolean }
	allItemsLoaded: { [key: string]: boolean }
	lastItemLoaded: { [key: string]: number }
	newestItemId: number
	syncNeeded: boolean

	starredCount: number
	unreadCount: number

	allItems: FeedItem[]
	recentItemIds: string[]

	selectedId?: string
	playingItem?: FeedItem
}

const state: ItemState = reactive({
	fetchingItems: {},
	allItemsLoaded: {},
	lastItemLoaded: {},
	newestItemId: 0,
	syncNeeded: false,

	starredCount: 0,
	unreadCount: 0,

	allItems: [],
	recentItemIds: [],

	selectedId: undefined,
	playingItem: undefined,
})

const getters = {
	starred(state: ItemState) {
		return state.allItems.filter((item) => item.starred)
	},
	unread(state: ItemState) {
		return state.allItems.filter((item) => item.unread)
	},
	selected(state: ItemState) {
		return state.allItems.find((item: FeedItem) => item.id === state.selectedId)
	},
	allItems(state: ItemState) {
		return state.allItems
	},
	recentItemIds(state: ItemState) {
		return state.recentItemIds
	},
	newestItemId(state: ItemState) {
		return state.newestItemId
	},
}

// timestamp of the latest fetch request
let latestFetchRequest = 0

export const actions = {
	/**
	 * Fetch Unread Items from Backend and call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit data
	 * @param param1 ActionArgs
	 * @param param1.start Start arg
	 */
	async [FEED_ITEM_ACTION_TYPES.FETCH_UNREAD](
		{ commit }: ActionParams<ItemState>,
		{ start }: { start: number } = { start: 0 },
	) {
		const requestId = Date.now()
		latestFetchRequest = requestId

		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'unread', fetching: true })

		const response = await ItemService.fetchUnread(start || state.lastItemLoaded.unread)

		// skip response if outdated
		if (latestFetchRequest !== requestId) {
			return
		}

		if (response?.data.newestItemId && response?.data.newestItemId !== state.newestItemId) {
			state.syncNeeded = true
		}

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response?.data.items)

		if (response?.data.items.length < 40) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'unread', loaded: true })
		} else {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'unread', loaded: false })
		}

		if (response?.data.items.length > 0) {
			const lastItem = response?.data.items[response?.data.items.length - 1].id
			commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'unread', lastItem })
		}
		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'unread', fetching: false })
	},

	/**
	 * Fetch All Items from Backend and call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit param
	 * @param param1 ActionArgs
	 * @param param1.start Start data
	 */
	async [FEED_ITEM_ACTION_TYPES.FETCH_ITEMS](
		{ commit }: ActionParams<ItemState>,
		{ start }: { start: number } = { start: 0 },
	) {
		const requestId = Date.now()
		latestFetchRequest = requestId

		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'all', fetching: true })

		const response = await ItemService.fetchAll(start || state.lastItemLoaded.all)

		// skip response if outdated
		if (latestFetchRequest !== requestId) {
			return
		}

		if (response?.data.newestItemId && response?.data.newestItemId !== state.newestItemId) {
			state.syncNeeded = true
		}

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response?.data.items)

		if (response?.data.items.length < 40) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'all', loaded: true })
		} else {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'all', loaded: false })
		}

		if (response?.data.items.length > 0) {
			const lastItem = response?.data.items[response?.data.items.length - 1].id
			commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'all', lastItem })
		}
		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'all', fetching: false })
	},

	/**
	 * Fetch Starred Items from Backend and call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit param
	 * @param param1 ActionArgs
	 * @param param1.start Start data
	 */
	async [FEED_ITEM_ACTION_TYPES.FETCH_STARRED](
		{ commit }: ActionParams<ItemState>,
		{ start }: { start: number } = { start: 0 },
	) {
		const requestId = Date.now()
		latestFetchRequest = requestId

		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'starred', fetching: true })
		const response = await ItemService.fetchStarred(start || state.lastItemLoaded.starred)

		// skip response if outdated
		if (latestFetchRequest !== requestId) {
			return
		}

		if (response?.data.newestItemId && response?.data.newestItemId !== state.newestItemId) {
			state.syncNeeded = true
		}

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response?.data.items)
		if (response?.data.starred) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, response?.data.starred)
		}

		if (response?.data.items.length < 40) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'starred', loaded: true })
		} else {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'starred', loaded: false })
		}
		if (response?.data.items.length > 0) {
			const lastItem = response?.data.items[response?.data.items.length - 1].id
			commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'starred', lastItem })
		}
		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'starred', fetching: false })
	},

	/**
	 * Fetch All Feed Items from Backend and call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit param
	 * @param param1 ActionArgs
	 * @param param1.start Start data
	 * @param param1.feedId ID of the feed
	 */
	async [FEED_ITEM_ACTION_TYPES.FETCH_FEED_ITEMS](
		{ commit }: ActionParams<ItemState>,
		{ feedId, start }: { feedId: number, start: number },
	) {
		const requestId = Date.now()
		latestFetchRequest = requestId

		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'feed-' + feedId, fetching: true })
		const response = await ItemService.fetchFeedItems(feedId, start || state.lastItemLoaded['feed-' + feedId])

		// skip response if outdated
		if (latestFetchRequest !== requestId) {
			return
		}

		if (response?.data.newestItemId && response?.data.newestItemId !== state.newestItemId) {
			state.syncNeeded = true
		}

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response?.data.items)
		if (response?.data.items.length < 40) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'feed-' + feedId, loaded: true })
		} else {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'feed-' + feedId, loaded: false })
		}
		if (response?.data.items.length > 0) {
			const lastItem = response?.data.items[response?.data.items.length - 1].id
			commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'feed-' + feedId, lastItem })
		}
		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'feed-' + feedId, fetching: false })
	},

	/**
	 * Fetch Folder Items from Backend and call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit param
	 * @param param1 ActionArgs
	 * @param param1.start Start data
	 * @param param1.folderId ID of the folder
	 */
	async [FEED_ITEM_ACTION_TYPES.FETCH_FOLDER_FEED_ITEMS](
		{ commit }: ActionParams<ItemState>,
		{ folderId, start }: { folderId: number, start: number },
	) {
		const requestId = Date.now()
		latestFetchRequest = requestId

		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'folder-' + folderId, fetching: true })
		const response = await ItemService.fetchFolderItems(folderId, start || state.lastItemLoaded['folder-' + folderId])

		// skip response if outdated
		if (latestFetchRequest !== requestId) {
			return
		}

		if (response?.data.newestItemId && response?.data.newestItemId !== state.newestItemId) {
			state.syncNeeded = true
		}

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response?.data.items)
		if (response?.data.items.length < 40) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'folder-' + folderId, loaded: true })
		} else {
			commit(FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED, { key: 'folder-' + folderId, loaded: false })
		}
		if (response?.data.items.length > 0) {
			const lastItem = response?.data.items[response?.data.items.length - 1].id
			commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'folder-' + folderId, lastItem })
		}
		commit(FEED_ITEM_MUTATION_TYPES.SET_FETCHING, { key: 'folder-' + folderId, fetching: false })
	},

	/**
	 * Sends message to Backend to mark as read, and then call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit action
	 * @param param0.dispatch Dispatch action
	 * @param param1 ActionArgs
	 * @param param1.item Item argument
	 */
	[FEED_ITEM_ACTION_TYPES.MARK_READ](
		{ commit, dispatch }: ActionParams<ItemState>,
		{ item }: { item: FeedItem },
	) {
		ItemService.markRead(item, true)

		if (item.unread) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, state.unreadCount - 1)

			dispatch(FEED_ACTION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: item.feedId, delta: -1 })
		}
		item.unread = false
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
	},

	/**
	 * Sends message to Backend to mark as unread, and then call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit action
	 * @param param0.dispatch Dispatch
	 * @param param1 ActionArgs
	 * @param param1.item Item
	 */
	[FEED_ITEM_ACTION_TYPES.MARK_UNREAD](
		{ commit, dispatch }: ActionParams<ItemState>,
		{ item }: { item: FeedItem },
	) {
		ItemService.markRead(item, false)

		if (!item.unread) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, state.unreadCount + 1)

			dispatch(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId: item.feedId, delta: +1 })
		}
		item.unread = true
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
	},

	/**
	 * Sends message to Backend to mark as starred, and then call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit action
	 * @param param1 ActionArgs
	 * @param param1.item Item
	 */
	[FEED_ITEM_ACTION_TYPES.STAR_ITEM](
		{ commit }: ActionParams<ItemState>,
		{ item }: { item: FeedItem },
	) {
		ItemService.markStarred(item, true)

		item.starred = true
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, state.starredCount + 1)
	},

	/**
	 * Sends message to Backend to remove mark as starred, and then call commit to update state
	 *
	 * @param param0 ActionParams
	 * @param param0.commit Commit action
	 * @param param1 ActionArgs
	 * @param param1.item Item
	 */
	[FEED_ITEM_ACTION_TYPES.UNSTAR_ITEM](
		{ commit }: ActionParams<ItemState>,
		{ item }: { item: FeedItem },
	) {
		ItemService.markStarred(item, false)

		item.starred = false
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, state.starredCount - 1)
	},
}

export const mutations = {
	[FEED_ITEM_MUTATION_TYPES.SET_SELECTED_ITEM](
		state: ItemState,
		{ id, key }: { id: string, key?: string },
	) {
		state.selectedId = id
		if (id && key !== 'recent') {
			state.recentItemIds = state.recentItemIds.filter((itemId) => itemId !== id)
			state.recentItemIds.unshift(id)
			if (state.recentItemIds.length > 20) {
				state.recentItemIds.pop()
			}
		}
	},

	[FEED_ITEM_MUTATION_TYPES.SET_PLAYING_ITEM](
		state: ItemState,
		item?: FeedItem,
	) {
		state.playingItem = item
	},

	[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](
		state: ItemState,
		items: FeedItem[],
	) {
		if (items) {
			let newestFetchedItemId = 0
			const newItems: FeedItem[] = []

			items.forEach((it) => {
				if (state.allItems.find((existing: FeedItem) => existing.id === it.id) === undefined) {
					if (!it.title) {
						it.title = it.url
					}
					newItems.push(it)
					if (state.newestItemId < Number(it.id)) {
						newestFetchedItemId = Number(it.id)
					}
				}
			})

			if (newItems.length > 0) {
				state.allItems = [...state.allItems, ...newItems]
			}

			if (newestFetchedItemId > state.newestItemId) {
				state.syncNeeded = true
			}
		}
	},

	[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT](
		state: ItemState,
		count: number,
	) {
		state.starredCount = count
	},

	[FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT](
		state: ItemState,
		count: number,
	) {
		state.unreadCount = count
	},

	[FEED_ITEM_MUTATION_TYPES.MODIFY_UNREAD_COUNT](
		state: ItemState,
		{ delta }: { delta: number },
	) {
		state.unreadCount += delta
	},

	[FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM](
		state: ItemState,
		{ item }: { item: FeedItem },
	) {
		const idx = state.allItems.findIndex((it) => it.id === item.id)
		Object.assign(state.allItems[idx], item)
	},

	[FEED_ITEM_MUTATION_TYPES.SET_FETCHING](
		state: ItemState,
		{ fetching, key }: { fetching: boolean, key: string },
	) {
		state.fetchingItems[key] = fetching
	},

	[FEED_ITEM_MUTATION_TYPES.SET_ALL_LOADED](
		state: ItemState,
		{ loaded, key }: { loaded: boolean, key: string },
	) {
		state.allItemsLoaded[key] = loaded
	},

	[FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED](
		state: ItemState,
		{ lastItem, key }: { lastItem: number, key: string },
	) {
		state.lastItemLoaded[key] = lastItem
	},

	[FEED_ITEM_MUTATION_TYPES.SET_NEWEST_ITEM_ID](
		state: ItemState,
		newestItemId: number,
	) {
		if (state.newestItemId !== newestItemId) {
			state.newestItemId = newestItemId
			state.allItemsLoaded = {}
		}
		state.syncNeeded = false
	},

	[FEED_ITEM_MUTATION_TYPES.RESET_ITEM_STATES](state: ItemState) {
		state.allItems = []
		state.allItemsLoaded = {}
		state.lastItemLoaded = {}
		state.newestItemId = 0
	},

	[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](
		state: ItemState,
		feed: Feed,
	) {
		state.allItems.forEach((item: FeedItem) => {
			if (item.feedId === feed.id) {
				item.unread = false
			}
		})
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
