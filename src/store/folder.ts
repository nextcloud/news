import _ from 'lodash'

import { AppState, ActionParams } from '../store'
import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import { FOLDER_MUTATION_TYPES, FEED_MUTATION_TYPES } from '../types/MutationTypes'
import { FolderService } from '../dataservices/folder.service'

export const FOLDER_ACTION_TYPES = {
	FETCH_FOLDERS: 'FETCH_FOLDERS',
	ADD_FOLDERS: 'ADD_FOLDER',
	DELETE_FOLDER: 'DELETE_FOLDER',

	FOLDER_SET_NAME: 'FOLDER_SET_NAME',
}

export type FolderState = {
	folders: Folder[]
}

const state: FolderState = {
	folders: [],
}

const getters = {
	folders(state: FolderState) {
		return state.folders
	},
}

export const actions = {
	async [FOLDER_ACTION_TYPES.FETCH_FOLDERS]({ commit }: ActionParams) {
		const folders = await FolderService.fetchAllFolders()

		commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, folders.data.folders)
	},
	async [FOLDER_ACTION_TYPES.ADD_FOLDERS](
		{ commit }: ActionParams,
		{ folder }: { folder: Folder },
	) {
		const response = await FolderService.createFolder({ name: folder.name })
		commit(FOLDER_MUTATION_TYPES.SET_FOLDERS, response.data.folders)
	},
	async [FOLDER_ACTION_TYPES.DELETE_FOLDER](
		{ commit }: ActionParams,
		{ folder }: { folder: Folder },
	) {
		await FolderService.deleteFolder({ id: folder.id })
		commit(FOLDER_MUTATION_TYPES.DELETE_FOLDER, folder)
	},
	async [FOLDER_ACTION_TYPES.FOLDER_SET_NAME](
		{ commit }: ActionParams,
		{ folder, name }: { folder: Folder, name: string },
	) {
		await FolderService.renameFolder({ id: folder.id, name })
		commit(FOLDER_MUTATION_TYPES.UPDATE_FOLDER, { id: folder.id, name })
	},
}

export const mutations = {
	[FOLDER_MUTATION_TYPES.SET_FOLDERS](
		state: AppState,
		folders: Folder[],
	) {
		folders.forEach(it => {
			it.feedCount = 0
			it.feeds = []
			state.folders.push(it)
		})
	},

	[FOLDER_MUTATION_TYPES.DELETE_FOLDER](
		state: AppState,
		folder: Folder,
	) {
		const index = state.folders.indexOf(folder)
		state.folders.splice(index, 1)
	},

	[FEED_MUTATION_TYPES.SET_FEEDS](
		state: AppState,
		feeds: Feed[],
	) {
		feeds.forEach(it => {
			const folder = state.folders.find((folder: Folder) => { return folder.id === it.folderId })
			if (folder) {
				folder.feeds.push(it)
				folder.feedCount += it.unreadCount
			}
		})
	},

	[FEED_MUTATION_TYPES.ADD_FEED](
		state: AppState,
		feed: Feed,
	) {
		const folder = state.folders.find((folder: Folder) => { return folder.id === feed.folderId })
		if (folder) {
			folder.feeds.push(feed)
			folder.feedCount += feed.unreadCount
		}
	},

	[FOLDER_MUTATION_TYPES.UPDATE_FOLDER](
		state: AppState,
		newFolder: Folder,
	) {
		const folder = state.folders.find((f: Folder) => { return f.id === newFolder.id })
		if (folder) {
			_.assign(folder, newFolder)
		}
	},

	[FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT](
		state: AppState,
		{ folderId, delta }: {folderId: number; delta: number },
	) {
		const folder = state.folders.find((f: Folder) => { return f.id === folderId })
		if (folder) {
			folder.feedCount += delta
		}
	},

	[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](
		state: AppState,
		feed: Feed,
	) {
		const folder = state.folders.find((folder: Folder) => { return folder.id === feed.folderId })

		if (folder) {
			folder.feedCount -= feed.unreadCount
		}
	},
}

export default {
	state,
	getters,
	actions,
	mutations,
}
