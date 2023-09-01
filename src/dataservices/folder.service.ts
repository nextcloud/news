import { AxiosResponse } from 'axios'
import axios from '@nextcloud/axios'

import { API_ROUTES } from '../types/ApiRoutes'

export class FolderService {

	/**
	 * Retrieves all of the folders from the Nextcloud News backend
	 *
	 * @return {AxiosResponse} Folders contained in data.folders property
	 */
	static fetchAllFolders(): Promise<AxiosResponse> {
		return axios.get(API_ROUTES.FOLDER)
	}

	/**
	 * Creates a new Folder in the Nextcloud News backend
	 *
	 * @param param0
	 * @param param0.name {String} New Folder Name
	 * @return {AxiosResponse} Folder info from backend in data.folders[0] property
	 */
	static createFolder({ name }: { name: string }): Promise<AxiosResponse> {
		return axios.post(API_ROUTES.FOLDER, { folderName: name })
	}

	/**
	 * Deletes a folder in the Nextcloud News backend (by id number)
	 *
	 * @param param0
	 * @param param0.id {number} id of folder to delete
	 * @return {AxiosResponse}
	 */
	static deleteFolder({ id }: { id: number }): Promise<AxiosResponse> {
		return axios.delete(API_ROUTES.FOLDER + '/' + id)
	}

}
