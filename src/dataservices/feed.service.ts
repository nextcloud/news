import { AxiosResponse } from 'axios'
import axios from '@nextcloud/axios'

import { API_ROUTES } from '../types/ApiRoutes'

export enum FEED_ORDER {
	OLDEST = 1,
	NEWEST = 0,
	DEFAULT = 2,
}

export enum FEED_UPDATE_MODE {
	IGNORE = 1,
	UNREAD = 0,
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
	 * @param param0 Data for the feed
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
	 * @param param0 Data for the feed
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
	 * @param param0 Data for the feed
	 * @param param0.feedId {Number} ID number of feed to update
	 * @param param0.pinned {Boolean} should be pinned (true) or not pinned (flse)
	 * @param param0.ordering {FEED_ORDER} sets feed order (0 = NEWEST, 1 = OLDEST, 2 = DEFAULT)
	 * @param param0.fullTextEnabled {Boolean} should be full text be enabled (true) or not (flse)
	 * @param param0.updateMode {FEED_UPDATE_MODE} sets updateMode (0 = UNREAD, 1 = IGNORE)
	 * @param param0.title {String} title of feed to display
	 * @return {AxiosResponse} Null value is returned on success
	 */
	static updateFeed({ feedId, pinned, ordering, fullTextEnabled, updateMode, title }: { feedId: number, pinned?: boolean, ordering?: FEED_ORDER, fullTextEnabled?: boolean, updateMode?: FEED_UPDATE_MODE, title?: string }): Promise<AxiosResponse> {
		return axios.patch(API_ROUTES.FEED + `/${feedId}`, {
			pinned,
			ordering,
			fullTextEnabled,
			updateMode,
			title,
		})
	}

	/**
	 * Move a feed to a different folder
	 *
	 * @param param0 Data for the feed
	 * @param param0.feedId {Number} ID number of feed to update
	 * @param param0.folderId {Number} ID number of folder to move feed to
	 * @return {AxiosResponse} Null value is returned on success
	 */
	static moveFeed({ feedId, folderId }: { feedId: number, folderId: number }): Promise<AxiosResponse> {
		return axios.patch(API_ROUTES.FEED + `/${feedId}`, {
			folderId,
		})
	}

	/**
	 * Deletes a feed
	 *
	 * @param param0 Data for the feed
	 * @param param0.feedId {Number} ID number of feed to delete
	 * @return {AxiosResponse} Null value is returned on success
	 */
	static deleteFeed({ feedId }: { feedId: number }): Promise<AxiosResponse> {
		return axios.delete(API_ROUTES.FEED + `/${feedId}`)
	}

}
