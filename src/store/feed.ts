import _ from 'lodash'

import { ActionParams, AppState } from '../store'
import { Feed } from '../types/Feed'
import { FOLDER_MUTATION_TYPES, FEED_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { FolderService } from '../dataservices/folder.service'
import { FEED_ORDER, FEED_UPDATE_MODE, FeedService } from '../dataservices/feed.service'
import { ItemService } from '../dataservices/item.service'

export const FEED_ACTION_TYPES = {
	ADD_FEED: 'ADD_FEED',
	FETCH_FEEDS: 'FETCH_FEEDS',
	FEED_MARK_READ: 'FEED_MARK_READ',

	FEED_SET_PINNED: 'FEED_SET_PINNED',
	FEED_SET_ORDERING: 'FEED_SET_ORDERING',
	FEED_SET_FULL_TEXT: 'FEED_SET_FULL_TEXT',
	FEED_SET_UPDATE_MODE: 'FEED_SET_UPDATE_MODE',
	FEED_SET_TITLE: 'FEED_SET_TITLE',

	FEED_DELETE: 'FEED_DELETE',
}

const state = {
	feeds: [],
}

const getters = {
	feeds(state: AppState) {
		return state.feeds
	},
}

export const actions = {
	async [FEED_ACTION_TYPES.FETCH_FEEDS]({ commit }: ActionParams) {
		const feeds = await FeedService.fetchAllFeeds()

		commit(FEED_MUTATION_TYPES.SET_FEEDS, feeds.data.feeds)
		commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, (feeds.data.feeds.reduce((total: number, feed: Feed) => {
			return total + feed.unreadCount
		}, 0)))
	},
	async [FEED_ACTION_TYPES.ADD_FEED](
		{ commit }: ActionParams,
		{ feedReq }: {
			feedReq: {
				url: string;
				folder?: { id: number; name?: string; },
				user?: string;
				password?: string;
			}
		},
	) {
		let url = feedReq.url.trim()
		if (!url.startsWith('http')) {
			url = 'https://' + url
		}

		let folderId
		if (feedReq.folder?.id === undefined && feedReq.folder?.name && feedReq.folder?.name !== '') {
			const response = await FolderService.createFolder({ name: feedReq.folder.name })
			folderId = response.data.folders[0].id
			commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, response.data.folders)
		} else {
			folderId = feedReq.folder?.id || 0
		}

		// Check that url is resolvable
		try {
			const response = await FeedService.addFeed({
				url,
				folderId,
				user: feedReq.user,
				password: feedReq.password,
			})

			commit(FEED_MUTATION_TYPES.ADD_FEED, response.data.feeds[0])
		} catch (e) {
			// TODO: show error to user if failure
			console.log(e)

		}
	},
	async [FEED_ACTION_TYPES.FEED_MARK_READ]({ commit }: ActionParams, { feed }: { feed: Feed }) {
		// want to fetch feed so that we can retrieve the "highestItemId"
		const response = await ItemService.fetchFeedItems(feed.id as number)
		await FeedService.markRead({ feedId: feed.id as number, highestItemId: response.data.items[0].id })

		commit(FEED_MUTATION_TYPES.SET_FEED_ALL_READ, feed.id)
	},

	async [FEED_ACTION_TYPES.FEED_SET_PINNED]({ commit }: ActionParams, { feed, pinned }: { feed: Feed, pinned: boolean }) {
		await FeedService.updateFeed({ feedId: feed.id as number, pinned })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, pinned })
	},

	async [FEED_ACTION_TYPES.FEED_SET_ORDERING]({ commit }: ActionParams, { feed, ordering }: { feed: Feed, ordering: FEED_ORDER }) {
		await FeedService.updateFeed({ feedId: feed.id as number, ordering })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, ordering })
	},

	async [FEED_ACTION_TYPES.FEED_SET_FULL_TEXT]({ commit }: ActionParams, { feed, fullTextEnabled }: { feed: Feed, fullTextEnabled: boolean }) {
		await FeedService.updateFeed({ feedId: feed.id as number, fullTextEnabled })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, fullTextEnabled })
	},

	async [FEED_ACTION_TYPES.FEED_SET_UPDATE_MODE]({ commit }: ActionParams, { feed, updateMode }: { feed: Feed, updateMode: FEED_UPDATE_MODE }) {
		await FeedService.updateFeed({ feedId: feed.id as number, updateMode })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, updateMode })
	},

	async [FEED_ACTION_TYPES.FEED_SET_TITLE]({ commit }: ActionParams, { feed, title }: { feed: Feed, title: string }) {
		await FeedService.updateFeed({ feedId: feed.id as number, title })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, title })
	},

	async [FEED_ACTION_TYPES.FEED_DELETE]({ commit }: ActionParams, { feed }: { feed: Feed }) {
		await FeedService.deleteFeed({ feedId: feed.id as number })

		commit(FEED_MUTATION_TYPES.FEED_DELETE, feed.id)
	},
}

export const mutations = {
	[FEED_MUTATION_TYPES.SET_FEEDS](state: AppState, feeds: Feed[]) {
		feeds.forEach(it => {
			state.feeds.push(it)
		})
	},
	[FEED_MUTATION_TYPES.ADD_FEED](state: AppState, feed: Feed) {
		state.feeds.push(feed)
	},
	[FEED_MUTATION_TYPES.UPDATE_FEED](state: AppState, newFeed: Feed) {
		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === newFeed.id
		})
		_.assign(feed, newFeed)
	},
	[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](state: AppState, feedId: number) {
		const priorFeed = state.feeds.find((stateFeed: Feed) => {
			return stateFeed.id === feedId
		})
		if (priorFeed) {
			const priorUnread = priorFeed?.unreadCount
			_.assign(priorFeed, { unreadCount: 0 })
			state.unreadCount -= priorUnread
		}
	},
	[FEED_MUTATION_TYPES.INCREASE_FEED_UNREAD_COUNT](state: AppState, feedId: number) {
		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === feedId
		})
		if (feed) {
			_.assign(feed, { unreadCount: feed.unreadCount + 1 })
		}
	},
	[FEED_MUTATION_TYPES.DECREASE_FEED_UNREAD_COUNT](state: AppState, feedId: number) {
		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === feedId
		})
		if (feed) {
			_.assign(feed, { unreadCount: feed.unreadCount - 1 })
		}
	},
	[FEED_MUTATION_TYPES.FEED_DELETE](state: AppState, feedId: number) {
		state.feeds = state.feeds.filter((feed: Feed) => {
			return feed.id !== feedId
		})
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
