import type { AxiosResponse } from '@nextcloud/axios'
import type { FeedItem } from '../types/FeedItem.ts'

import axios from '@nextcloud/axios'
import { API_ROUTES } from '../types/ApiRoutes.ts'
import { FEED_ORDER } from './../enums/index.ts'
import store from './../store/app.ts'
import feedstore from './../store/feed.ts'

export const ITEM_TYPES = {
	FEED: 0,
	FOLDER: 1,
	STARRED: 2,
	ALL: 3,
	UNREAD: 6,
}

export class ItemService {
	/**
	 * Makes backend call to retrieve all items
	 *
	 * @param start (id of last starred item loaded)
	 * @return response object containing backend request response
	 */
	static async fetchAll(start: number): Promise<AxiosResponse> {
		return await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: store.state.oldestFirst,
				search: '',
				showAll: true,
				type: ITEM_TYPES.ALL,
				offset: start,
			},
		})
	}

	/**
	 * Makes backend call to retrieve starred items
	 *
	 * @param start (id of last starred item loaded)
	 * @return response object containing backend request response
	 */
	static async fetchStarred(start: number): Promise<AxiosResponse> {
		return await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: store.state.oldestFirst,
				search: '',
				showAll: store.state.showAll,
				type: ITEM_TYPES.STARRED,
				offset: start,
			},
		})
	}

	/**
	 * Makes backend call to retrieve unread items
	 *
	 * @param start (id of last unread item loaded)
	 * @return response object containing backend request response
	 */
	static async fetchUnread(start: number): Promise<AxiosResponse> {
		return await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: store.state.oldestFirst,
				search: '',
				showAll: store.state.showAll,
				type: ITEM_TYPES.UNREAD,
				offset: start,
			},
		})
	}

	/**
	 * Makes backend call to retrieve items from a specific feed
	 *
	 * @param feedId id number of feed to retrieve items for
	 * @param start (id of last unread item loaded)
	 * @return response object containing backend request response
	 */
	static async fetchFeedItems(feedId: number, start?: number): Promise<AxiosResponse> {
		let oldestFirst
		switch (feedstore.state.ordering['feed-' + feedId]) {
			case FEED_ORDER.OLDEST:
				oldestFirst = true
				break
			case FEED_ORDER.NEWEST:
				oldestFirst = false
				break
			case FEED_ORDER.DEFAULT:
			default:
				oldestFirst = store.state.oldestFirst
		}

		return await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst,
				search: '',
				showAll: store.state.showAll,
				type: ITEM_TYPES.FEED,
				offset: start,
				id: feedId,
			},
		})
	}

	/**
	 * Makes backend call to retrieve items from a specific folder
	 *
	 * @param folderId id number of folder to retrieve items for
	 * @param start (id of last unread item loaded)
	 * @return response object containing backend request response
	 */
	static async fetchFolderItems(folderId: number, start: number): Promise<AxiosResponse> {
		return await axios.get(API_ROUTES.ITEMS, {
			params: {
				limit: 40,
				oldestFirst: store.state.oldestFirst,
				search: '',
				showAll: store.state.showAll,
				type: ITEM_TYPES.FOLDER,
				offset: start,
				id: folderId,
			},
		})
	}

	/**
	 * Makes backend call to mark item as read/unread in DB
	 *
	 * @param item FeedItem (containing id) that will be marked as read/unread
	 * @param read if read or not
	 */
	static async markRead(item: FeedItem, read: boolean): Promise<void> {
		axios.post(API_ROUTES.ITEMS + `/${item.id}/read`, {
			isRead: read,
		})
	}

	/**
	 * Makes backend call to mark item as starred/unstarred in DB
	 *
	 * @param item FeedItem (containing id) that will be marked as starred/unstarred
	 * @param read if starred or not
	 */
	static async markStarred(item: FeedItem, read: boolean): Promise<void> {
		axios.post(API_ROUTES.ITEMS + `/${item.feedId}/${item.guidHash}/star`, {
			isStarred: read,
		})
	}
}
