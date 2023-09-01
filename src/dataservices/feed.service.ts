import { AxiosResponse } from 'axios'
import axios from '@nextcloud/axios'

import { API_ROUTES } from '../types/ApiRoutes'

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

}
