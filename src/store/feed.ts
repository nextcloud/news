import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

import { ActionParams, AppState } from '../store'
import { Feed } from '../types/Feed'

export const FEED_ACTION_TYPES = {
	ADD_FEED: 'ADD_FEED',
	FETCH_FEEDS: 'FETCH_FEEDS',
}

export const FEED_MUTATION_TYPES = {
	ADD_FEED: 'ADD_FEED',
	SET_FEEDS: 'SET_FEEDS',
}

const state = {
	feeds: [],
}

const getters = {
	feeds(state: AppState) {
		return state.feeds
	},
}

const feedUrl = generateUrl('/apps/news/feeds')

export const actions = {
	async [FEED_ACTION_TYPES.FETCH_FEEDS]({ commit }: ActionParams) {
		const feeds = await axios.get(
			generateUrl('/apps/news/feeds'),
		)

		commit(FEED_MUTATION_TYPES.SET_FEEDS, feeds.data.feeds)
	},
	async [FEED_ACTION_TYPES.ADD_FEED](
		{ commit }: ActionParams, 
		{ feedReq }: { feedReq: { url: string; folder?: { id: number }, user?: string; password?: string; } })
	{
		let url = feedReq.url.trim()
		if (!url.startsWith('http')) {
			url = 'https://' + url
		}

		/**
      if (title !== undefined) {
          title = title.trim();
      }
		 */

		const feed: Feed = {
			url,
			folderId: feedReq.folder?.id || 0,
			title: undefined,
			unreadCount: 0,
			autoDiscover: undefined, // TODO: autodiscover?
		}

		// Check that url is resolvable
		try {
			const response = await axios.post(feedUrl, {
				url: feed.url,
				parentFolderId: feed.folderId,
				title: null,
				user: feedReq.user ? feedReq.user : null,
				password: feedReq.password ? feedReq.password : null,
				fullDiscover: feed.autoDiscover,
			})

			commit(FEED_MUTATION_TYPES.ADD_FEED, response.data.feeds[0])
		} catch(e) {
				// TODO: show error to user if failure
				console.log(e);
				return;
		}
	},
}

export const mutations = {
	[FEED_MUTATION_TYPES.SET_FEEDS](state: AppState, feeds: Feed[]) {
		feeds.forEach(it => {
			state.feeds.push(it)
		})
	},
	[FEED_MUTATION_TYPES.ADD_FEED](state: AppState, feed: Feed) {
		state.feeds.push(feed);
	}
}

export default {
	state,
	getters,
	actions,
	mutations,
}
