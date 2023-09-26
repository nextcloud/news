import { AxiosResponse } from 'axios'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

export class ShareService {

	/**
	 * Retrieves all of users matching the search term
	 *
	 * @param query {String} search string
	 * @return {AxiosResponse} Folders contained in data.folders property
	 */
	static fetchUsers(query: string): Promise<AxiosResponse> {
		return axios.get(generateOcsUrl(`apps/files_sharing/api/v1/sharees?search=${query}&itemType=news_item&perPage=5/`))
	}

	static async share(id: number, users: string[]): Promise<boolean> {
		const promises = []
		for (const shareName of users) {
			promises.push(axios.post(`items/${id}/share/${shareName}`))
		}

		await Promise.all(promises)

		return true
	}

}
