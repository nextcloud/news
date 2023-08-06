import { Feed } from '@/types/Feed'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

import { AppState, ActionParams } from '../store'
import { Folder } from '../types/Folder'
import { FEED_MUTATION_TYPES } from './feed'

export const FOLDER_ACTION_TYPES = {
	FETCH_FOLDERS: 'FETCH_FOLDERS',
	ADD_FOLDERS: 'ADD_FOLDER',
	DELETE_FOLDER: 'DELETE_FOLDER',
}

export const FOLDER_MUTATION_TYPES = {
	SET_FOLDERS: 'SET_FOLDERS',
	DELETE_FOLDER: 'DELETE_FOLDER',
}

const state = {
	folders: [],
}

const getters = {
	folders(state: AppState) {
		return state.folders
	},
}

const folderUrl = generateUrl('/apps/news/folders')

export const actions = {
	async [FOLDER_ACTION_TYPES.FETCH_FOLDERS]({ commit }: ActionParams) {
		const folders = await axios.get(
			generateUrl('/apps/news/folders'),
		)

		commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, folders.data.folders)
	},
	async [FOLDER_ACTION_TYPES.ADD_FOLDERS]({ commit }: ActionParams, { folder }: { folder: Folder}) {
		const response = await axios.post(folderUrl, { folderName: folder.name })
		commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, response.data.folders)
	},
	async [FOLDER_ACTION_TYPES.DELETE_FOLDER]({ commit }: ActionParams, { folder }: { folder: Folder}) {
		/**
		 * TODO: look into reversiblyDelete?
      this.getByFolderId(folderId).forEach(function (feed) {
          promises.push(self.reversiblyDelete(feed.id, false, true));
      });
		 */
		await axios.delete(folderUrl + '/' + folder.id)
		commit(FOLDER_MUTATION_TYPES.DELETE_FOLDER, folder)
	},
}

export const mutations = {
	[FOLDER_MUTATION_TYPES.SET_FOLDERS](state: AppState, folders: Folder[]) {
		folders.forEach(it => {
			it.feedCount = 0
			it.feeds = []
			state.folders.push(it)
		})
	},
	[FOLDER_MUTATION_TYPES.DELETE_FOLDER](state: AppState, folder: Folder) {
		const index = state.folders.indexOf(folder)
		state.folders.splice(index, 1)
	},
	[FEED_MUTATION_TYPES.SET_FEEDS](state: AppState, feeds: Feed[]) {
		feeds.forEach(it => {
			const folder = state.folders.find(folder => folder.id === it.folderId)
			if (folder) {
				folder.feeds.push(it)
				folder.feedCount += it.unreadCount
			}
		});
	},
	[FEED_MUTATION_TYPES.ADD_FEED](state: AppState, feed: Feed) {
		const folder = state.folders.find(folder => folder.id === feed.folderId)
		if (folder) {
			folder.feeds.push(feed)
			folder.feedCount += feed.unreadCount
		}
	}
}

export default {
	state,
	getters,
	actions,
	mutations,
}
