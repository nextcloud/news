import { AxiosResponse } from 'axios'
import axios from '@nextcloud/axios'

import { API_ROUTES } from '../types/ApiRoutes'

export const FEED_ORDER = {
	OLDEST: 1,
	NEWEST: 0,
	DEFAULT: 2,
}

export const FEED_UPDATE_MODE = {
	IGNORE: 1,
	UNREAD: 0,
}

export class FeedService {

	/**
	 * Retrieves all Feed info from the Nextcloud News backend
	 *
	 * @return {AxiosResponse} Feed info stored in array property data.feeds
	 */
	static fetchAllFeeds(): Promise<AxiosResponse> {
		return axios.get(API_ROUTES.FEED)
	}

	/**
	 * Attempts to add a feed to the Nextcloud News backend
	 * NOTE: this can fail if feed URL is not resolvable
	 *
	 * @param param0
	 * @param param0.url {String} url of the feed to add
	 * @param param0.folderId {number} id number of folder to add feed to
	 * @param param0.user {String} http auth username required for accessing feed
	 * @param param0.password {String} http auth password required for accessing feed
	 * @return {AxiosResponse} Feed info stored in data.feeds[0] property
	 */
	static addFeed({ url, folderId, user, password }: { url: string; folderId: number; user?: string; password?: string }): Promise<AxiosResponse> {
		return axios.post(API_ROUTES.FEED, {
			url,
			parentFolderId: folderId,
			title: null, // TODO: let user define feed title on create?
			user: user || null,
			password: password || null,
			fullDiscover: undefined, // TODO: autodiscover?
		})
	}

	/**
	 * Marks all items in feed, started with highestReadId
	 *
	 * @param param0
	 * @param param0.feedId {Number} ID number of feed to mark items as read
	 * @param param0.highestItemId {Number} ID number of the (most recent?) feed item to mark as read (all older items will be marked as read)
	 * @return {AxiosResponse} Updated feed info (unreadCount = 0) stored in data.feeds[0] property
	 */
	static markRead({ feedId, highestItemId }: { feedId: number, highestItemId: number }): Promise<AxiosResponse> {
		return axios.post(API_ROUTES.FEED + `/${feedId}/read`, {
			highestItemId,
		})
	}

	/**
	 * Update a feeds properties
	 *
	 * @param param0
	 * @param param0.feedId {Number} ID number of feed to mark items as read
	 * @param param0.pinned {Boolean} should be pinned (true) or not pinned (flse)
	 * @return {AxiosResponse} Updated feed info based on parameters provided
	 */
	static updateFeed({ feedId, pinned }: { feedId: number, pinned?: boolean }): Promise<AxiosResponse> {
		return axios.patch(API_ROUTES.FEED + `/${feedId}`, {
			pinned,
		})
	}

}
