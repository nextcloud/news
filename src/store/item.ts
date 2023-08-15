import axios from '@nextcloud/axios'

import { ActionParams } from '../store'
import { FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { API_ROUTES } from '../types/ApiRoutes'
import { FeedItem } from '../types/FeedItem'

export const FEED_ITEM_ACTION_TYPES = {
	FETCH_STARRED: 'FETCH_STARRED',
}

export type ItemState = {
	fetchingItems: boolean;
	starredLoaded: boolean;

	allItems: FeedItem[];
	starredItems: FeedItem[];
}

const state: ItemState = {
	fetchingItems: false,
	starredLoaded: false,

	allItems: [],
	starredItems: [],
}

const getters = {
	starred(state: ItemState) {
		return state.starredItems
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

		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED, response.data.items)

		if (response.data.items.length < 40) {
			state.starredLoaded = true
		}
		state.fetchingItems = false
	},
}

export const mutations = {
	[FEED_ITEM_MUTATION_TYPES.SET_STARRED](state: ItemState, items: FeedItem[]) {
		items.forEach(it => {
			state.starredItems.push(it)
		})
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
