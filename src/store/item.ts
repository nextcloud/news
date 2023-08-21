import axios from '@nextcloud/axios'

import { ActionParams } from '../store'
import { FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { API_ROUTES } from '../types/ApiRoutes'
import { FeedItem } from '../types/FeedItem'

export const FEED_ITEM_ACTION_TYPES = {
	FETCH_STARRED: 'FETCH_STARRED',
	FETCH_UNREAD: 'FETCH_UNREAD',
	MARK_READ: 'MARK_READ',
	MARK_UNREAD: 'MARK_UNREAD',
	STAR_ITEM: 'STAR_ITEM',
	UNSTAR_ITEM: 'UNSTAR_ITEM',
	SET_SELECTED_ITEM: 'SET_SELECTED_ITEM',
}

export type ItemState = {
	fetchingItems: { [key: string]: boolean };
	allItemsLoaded: { [key: string]: boolean };
	starredLoaded: boolean;

	starredCount: number;
	unreadCount: number;

	allItems: FeedItem[];

	selectedId?: string;
}

const state: ItemState = {
	fetchingItems: {},
	allItemsLoaded: {},
	starredLoaded: false,

	starredCount: 0,
	unreadCount: 0,

	allItems: [],
	selectedId: undefined,
}

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
}

export const actions = {
	async [FEED_ITEM_ACTION_TYPES.SET_SELECTED_ITEM]({ commit }: ActionParams, { id }: { id: string }) {
		state.selectedId = id
	},
	async [FEED_ITEM_ACTION_TYPES.FETCH_UNREAD]({ commit }: ActionParams, { start }: { start: number } = { start: 0 }) {
		if (state.allItems.filter((item) => item.unread).length === 0) {
			state.fetchingItems.unread = true
		}
		const response = await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: false,
				search: '',
				showAll: false,
				type: 6,
				offset: start,
			},
		})

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response.data.items)

		if (response.data.items.length < 40) {
			state.allItemsLoaded.unread = true
		}
		state.fetchingItems.unread = false
	},
	async [FEED_ITEM_ACTION_TYPES.FETCH_STARRED]({ commit }: ActionParams, { start }: { start: number } = { start: 0 }) {
		state.fetchingItems.starred = true
		const response = await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: false,
				search: '',
				showAll: false,
				type: 2,
				offset: start,
			},
		})

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response.data.items)
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, response.data.starred)

		if (response.data.items.length < 40) {
			state.starredLoaded = true
		}
		state.fetchingItems.starred = false
	},
	[FEED_ITEM_ACTION_TYPES.MARK_READ]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.id}/read`, {
			isRead: true,
		})
		if (item.unread) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, state.unreadCount - 1)
		}
		item.unread = false
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
	},
	[FEED_ITEM_ACTION_TYPES.MARK_UNREAD]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.id}/read`, {
			isRead: false,
		})
		if (!item.unread) {
			commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, state.unreadCount + 1)
		}
		item.unread = true
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
	},
	[FEED_ITEM_ACTION_TYPES.STAR_ITEM]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.feedId}/${item.guidHash}/star`, {
			isStarred: true,
		})
		item.starred = true
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, state.starredCount + 1)
	},
	[FEED_ITEM_ACTION_TYPES.UNSTAR_ITEM]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.feedId}/${item.guidHash}/star`, {
			isStarred: false,
		})
		item.starred = false
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, state.starredCount - 1)
	},
}

export const mutations = {
	[FEED_ITEM_MUTATION_TYPES.SET_ITEMS](state: ItemState, items: FeedItem[]) {
		items.forEach(it => {
			if (state.allItems.find((existing: FeedItem) => existing.id === it.id) === undefined) {
				state.allItems.push(it)
			}
		})
	},
	[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT](state: ItemState, count: number) {
		state.starredCount = count
	},
	[FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT](state: ItemState, count: number) {
		state.unreadCount = count
	},
	[FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM](state: ItemState, { item }: { item: FeedItem }) {
		const idx = state.allItems.findIndex((it) => it.id === item.id)
		state.allItems.splice(idx, 1, item)
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
