import axios from '@nextcloud/axios'

import { ActionParams } from '../store'
import { FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { API_ROUTES } from '../types/ApiRoutes'

export const FEED_ITEM_ACTION_TYPES = {
	FETCH_STARRED: 'FETCH_STARRED',
}

export type ItemState = {
	allItems: any[];
	starredItems: any[];
}

const state: ItemState = {
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
		const response = await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: false,
				search: '',
				showAll: false,
				type: 2,
			},
		})

		commit(FEED_ITEM_MUTATION_TYPES.SET_STARRED, response.data.items)
	},
}

export const mutations = {
	[FEED_ITEM_MUTATION_TYPES.SET_STARRED](state: ItemState, items: any[]) {
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
