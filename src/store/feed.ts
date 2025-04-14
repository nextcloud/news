import { reactive } from 'vue'
import _ from 'lodash'

import { ActionParams } from '../store'
import { Feed } from '../types/Feed'
import { FOLDER_MUTATION_TYPES, FEED_MUTATION_TYPES, FEED_ITEM_MUTATION_TYPES } from '../types/MutationTypes'
import { FolderService } from '../dataservices/folder.service'
import { FEED_ORDER, FEED_UPDATE_MODE, FeedService } from '../dataservices/feed.service'

export const FEED_ACTION_TYPES = {
	ADD_FEED: 'ADD_FEED',
	MOVE_FEED: 'MOVE_FEED',
	FETCH_FEEDS: 'FETCH_FEEDS',
	FEED_MARK_READ: 'FEED_MARK_READ',

	FEED_SET_PINNED: 'FEED_SET_PINNED',
	FEED_SET_ORDERING: 'FEED_SET_ORDERING',
	FEED_SET_FULL_TEXT: 'FEED_SET_FULL_TEXT',
	FEED_SET_UPDATE_MODE: 'FEED_SET_UPDATE_MODE',
	FEED_SET_TITLE: 'FEED_SET_TITLE',

	MODIFY_FEED_UNREAD_COUNT: 'MODIFY_FEED_UNREAD_COUNT',

	FEED_DELETE: 'FEED_DELETE',
}

export type FeedState = {
	feeds: Feed[];
	unreadCount: number;
	newestItemId: number;
	ordering: { [key: string]: number };
}

const state: FeedState = reactive({
	feeds: [],
	unreadCount: 0,
	newestItemId: 0,
	ordering: {},
})

const getters = {
	feeds(state: FeedState) {
		return state.feeds
	},
}

export const actions = {
	async [FEED_ACTION_TYPES.FETCH_FEEDS]({ commit }: ActionParams<FeedState>) {
		const response = await FeedService.fetchAllFeeds()
		if (response.data.newestItemId) {
			commit(FEED_MUTATION_TYPES.SET_NEWEST_ITEM_ID, response.data.newestItemId)
			commit(FEED_ITEM_MUTATION_TYPES.SET_NEWEST_ITEM_ID, response.data.newestItemId)
		}

		commit(FEED_MUTATION_TYPES.SET_FEEDS, response.data.feeds)
		commit(FEED_ITEM_MUTATION_TYPES.SET_UNREAD_COUNT, (response.data.feeds.reduce((total: number, feed: Feed) => {
			return total + feed.unreadCount
		}, 0)))
	},

	async [FEED_ACTION_TYPES.ADD_FEED](
		{ commit }: ActionParams<FeedState>,
		{ feedReq }: {
			feedReq: {
				url: string;
				folder?: { id: number; name?: string; },
				autoDiscover: boolean;
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

		try {
			const response = await FeedService.addFeed({
				url,
				folderId,
				autoDiscover: feedReq.autoDiscover,
				user: feedReq.user,
				password: feedReq.password,
			})

			commit(FEED_MUTATION_TYPES.ADD_FEED, response.data.feeds[0])
			return response
		} catch (e) {
			if (e && typeof e === 'object' && 'response' in e) {
				return e.response
			}
			return { status: undefined }
		}
	},

	async [FEED_ACTION_TYPES.MOVE_FEED](
		state: FeedState,
		{ feedId, folderId }: { feedId: number, folderId: number },
	) {
		// Check that url is resolvable
		try {
			const response = await FeedService.moveFeed({
				feedId,
				folderId,
			})

			if (!response) {
				console.error('error moving feed %d', feedId)
			}
		} catch (e) {
			// TODO: show error to user if failure
			console.error(e)
		}
	},

	async [FEED_ACTION_TYPES.FEED_MARK_READ](
		{ commit }: ActionParams<FeedState>,
		{ feed }: { feed: Feed },
	) {
		await FeedService.markRead({ feedId: feed.id as number, highestItemId: state.newestItemId })

		if (feed.folderId) {
			commit(FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT, { folderId: feed.folderId, delta: -feed.unreadCount })
		}
		commit(FEED_ITEM_MUTATION_TYPES.MODIFY_UNREAD_COUNT, { delta: -feed.unreadCount })

		commit(FEED_MUTATION_TYPES.SET_FEED_ALL_READ, feed)
	},

	async [FEED_ACTION_TYPES.FEED_SET_PINNED](
		{ commit }: ActionParams<FeedState>,
		{ feed, pinned }: { feed: Feed, pinned: boolean },
	) {
		await FeedService.updateFeed({ feedId: feed.id as number, pinned })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, pinned })
	},

	async [FEED_ACTION_TYPES.FEED_SET_ORDERING](
		{ commit }: ActionParams<FeedState>,
		{ feed, ordering }: { feed: Feed, ordering: FEED_ORDER },
	) {
		state.ordering = { ...state.ordering, ['feed-' + feed.id]: ordering }
		commit(FEED_ITEM_MUTATION_TYPES.SET_LAST_ITEM_LOADED, { key: 'feed-' + feed.id, lastItem: undefined })
		await FeedService.updateFeed({ feedId: feed.id as number, ordering })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, ordering })
	},

	async [FEED_ACTION_TYPES.FEED_SET_FULL_TEXT](
		{ commit }: ActionParams<FeedState>,
		{ feed, fullTextEnabled }: { feed: Feed, fullTextEnabled: boolean },
	) {
		await FeedService.updateFeed({ feedId: feed.id as number, fullTextEnabled })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, fullTextEnabled })
	},

	async [FEED_ACTION_TYPES.FEED_SET_UPDATE_MODE](
		{ commit }: ActionParams<FeedState>,
		 { feed, updateMode }: { feed: Feed, updateMode: FEED_UPDATE_MODE },
	) {
		await FeedService.updateFeed({ feedId: feed.id as number, updateMode })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, updateMode })
	},

	async [FEED_ACTION_TYPES.FEED_SET_TITLE](
		{ commit }: ActionParams<FeedState>,
		{ feed, title }: { feed: Feed, title: string },
	) {
		await FeedService.updateFeed({ feedId: feed.id as number, title })

		commit(FEED_MUTATION_TYPES.UPDATE_FEED, { id: feed.id, title })
	},

	async [FEED_ACTION_TYPES.FEED_DELETE](
		{ commit }: ActionParams<FeedState>,
		{ feed }: { feed: Feed },
	) {
		await FeedService.deleteFeed({ feedId: feed.id as number })

		if (feed.folderId) {
			commit(FOLDER_MUTATION_TYPES.REMOVE_FOLDER_FEED, { feedId: feed.id, folderId: feed.folderId, unreadCount: feed.unreadCount })
		}

		commit(FEED_MUTATION_TYPES.FEED_DELETE, feed.id)
	},

	async [FEED_ACTION_TYPES.MODIFY_FEED_UNREAD_COUNT](
		{ commit, state }: ActionParams<FeedState>,
		 { feedId, delta }: { feedId: number, delta: number },
	) {
		commit(FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT, { feedId, delta })

		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === feedId
		})

		if (feed?.folderId) {
			commit(FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT, { folderId: feed?.folderId, delta })
		}

	},
}

export const mutations = {
	[FEED_MUTATION_TYPES.SET_FEEDS](
		state: FeedState,
		feeds: Feed[],
	) {
		state.feeds = feeds.sort((a, b) => {
			if (!a.title) return -1 // sort undefined title at top
			if (!b.title) return 1
			return a.title.localeCompare(b.title, undefined, { sensitivity: 'base' })
		}).map(it => {
			if (typeof it?.ordering === 'number') {
				state.ordering['feed-' + it.id] = it.ordering
			}
			return it
		})
	},

	[FEED_MUTATION_TYPES.ADD_FEED](
		state: FeedState,
		 feed: Feed,
	) {
		state.feeds.push(feed)
	},

	[FEED_MUTATION_TYPES.UPDATE_FEED](
		state: FeedState,
		 newFeed: Feed,
	) {
		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === newFeed.id
		})
		_.assign(feed, newFeed)
	},

	[FEED_MUTATION_TYPES.SET_NEWEST_ITEM_ID](
		state: FeedState,
		newestItemId: number,
	) {
		state.newestItemId = newestItemId
	},

	[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](
		state: FeedState,
		feed: Feed,
	) {
		const priorFeed = state.feeds.find((stateFeed: Feed) => {
			return stateFeed.id === feed.id
		})
		if (priorFeed) {
			const priorUnread = priorFeed?.unreadCount
			_.assign(priorFeed, { unreadCount: 0 })
			state.unreadCount -= priorUnread
		}
	},

	[FEED_MUTATION_TYPES.MODIFY_FEED_UNREAD_COUNT](
		state: FeedState,
		{ feedId, delta }: { feedId: number, delta: number },
	) {
		const feed = state.feeds.find((feed: Feed) => {
			return feed.id === feedId
		})
		if (feed) {
			_.assign(feed, { unreadCount: feed.unreadCount + delta })
		}
	},

	[FEED_MUTATION_TYPES.FEED_DELETE](
		state: FeedState,
		feedId: number,
	) {
		const updatedFeeds = state.feeds.filter((feed: Feed) => {
			return feed.id !== feedId
		})
		state.feeds = [...updatedFeeds]
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
