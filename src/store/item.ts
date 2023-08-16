import axios from '@nextcloud/axios'

import { ActionParams } from '../store'
import { FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { API_ROUTES } from '../types/ApiRoutes'
import { FeedItem } from '../types/FeedItem'

export const FEED_ITEM_ACTION_TYPES = {
	FETCH_STARRED: 'FETCH_STARRED',
	MARK_READ: 'MARK_READ',
	MARK_UNREAD: 'MARK_UNREAD',
	STAR_ITEM: 'STAR_ITEM',
	UNSTAR_ITEM: 'UNSTAR_ITEM',
}

export type ItemState = {
	fetchingItems: boolean;
	starredLoaded: boolean;

	starredCount: number;

	allItems: FeedItem[];
}

const state: ItemState = {
	fetchingItems: false,
	starredLoaded: false,

	starredCount: 0,

	allItems: [],
}

const getters = {
	starred(state: ItemState) {
		return state.allItems.filter((item) => item.starred)
	},
}

export const actions = {
	async [FEED_ITEM_ACTION_TYPES.FETCH_STARRED]({ commit }: ActionParams) {
		state.fetchingItems = true
		const response = await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: false,
				search: '',
				showAll: false,
				type: 2,
				offset: 0,
			},
		})

		commit(FEED_ITEM_MUTATION_TYPES.SET_ITEMS, response.data.items)
		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT, response.data.starred)

		if (response.data.items.length < 40) {
			state.starredLoaded = true
		}
		state.fetchingItems = false
	},
	[FEED_ITEM_ACTION_TYPES.MARK_READ]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.id}/read`, {
			isRead: true,
		})
		item.unread = false
		commit(FEED_ITEM_MUTATION_TYPES.UPDATE_ITEM, { item })
	},
	[FEED_ITEM_ACTION_TYPES.MARK_UNREAD]({ commit }: ActionParams, { item }: { item: FeedItem}) {
		axios.post(API_ROUTES.ITEMS + `/${item.id}/read`, {
			isRead: false,
		})
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
			state.allItems.push(it)
		})
	},
	[FEED_ITEM_MUTATION_TYPES.SET_STARRED_COUNT](state: ItemState, count: number) {
		state.starredCount = count
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
