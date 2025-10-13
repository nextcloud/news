import type { AppState } from '../../../../src/store/index.ts'
import type { Folder } from '../../../../src/types/Folder.ts'

import { describe, expect, it, vi } from 'vitest'
import { FolderService } from '../../../../src/dataservices/folder.service'
import { actions, FOLDER_ACTION_TYPES, mutations } from '../../../../src/store/folder.ts'
import { FEED_MUTATION_TYPES, FOLDER_MUTATION_TYPES } from '../../../../src/types/MutationTypes.ts'

vi.mock('@nextcloud/router')

describe('folder.ts', () => {
	'use strict'

	describe('actions', () => {
		it('FETCH_FOLDERS should call FolderService.fetchAllFolders and then commit folders returned to state', async () => {
			FolderService.fetchAllFolders = vi.fn();
			(FolderService.fetchAllFolders as any).mockResolvedValue({ data: { folders: [] } })

			const commit = vi.fn()

			await actions[FOLDER_ACTION_TYPES.FETCH_FOLDERS]({ commit })
			expect(FolderService.fetchAllFolders).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('ADD_FOLDERS should call FolderService.createFolder and then commit the folders returned to state', async () => {
			FolderService.createFolder = vi.fn();
			(FolderService.createFolder as any).mockResolvedValue({ data: { folders: [] } })

			const folder = {} as Folder
			const commit = vi.fn()

			await actions[FOLDER_ACTION_TYPES.ADD_FOLDERS]({ commit } as any, { folder })
			expect(FolderService.createFolder).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('DELETE_FOLDER should call FolderService.deleteFolder and then commit deleted folder to state', async () => {
			FolderService.deleteFolder = vi.fn();
			(FolderService.deleteFolder as any).mockResolvedValue()

			const folder = {} as Folder
			const commit = vi.fn()

			await actions[FOLDER_ACTION_TYPES.DELETE_FOLDER]({ commit } as any, { folder })
			expect(FolderService.deleteFolder).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('FOLDER_SET_NAME should call FolderService.renameFolder and then commit deleted folder to state', async () => {
			FolderService.renameFolder = vi.fn();
			(FolderService.renameFolder as any).mockResolvedValue()

			const folder = {} as Folder
			const commit = vi.fn()

			await actions[FOLDER_ACTION_TYPES.FOLDER_SET_NAME]({ commit } as any, { folder, name: 'newName' } as any)
			expect(FolderService.renameFolder).toBeCalledWith({ id: folder.id, name: 'newName' })
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		it('SET_FOLDERS should add the passed in folders to the state', () => {
			const state = { folders: [] as Folder[] } as AppState
			let folders = [] as Folder[]

			mutations[FOLDER_MUTATION_TYPES.SET_FOLDERS](state, folders)
			expect(state.folders.length).toEqual(0)

			folders = [{ name: 'test' }] as Folder[]

			mutations[FOLDER_MUTATION_TYPES.SET_FOLDERS](state, folders)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0]).toEqual(folders[0])
		})

		it('DELETE_FOLDER should remove the passed in folder from the state', () => {
			const state = { folders: [{ name: 'test' }] as Folder[] } as AppState
			const folders = [state.folders[0]] as Folder[]

			mutations[FOLDER_MUTATION_TYPES.DELETE_FOLDER](state, folders)
			expect(state.folders.length).toEqual(0)
		})

		it('SET_FEEDS should add the feed to the folder in the state', () => {
			const state = { folders: [{ name: 'test', id: 123 }] as Folder[] } as AppState
			const feeds = [{ id: 345, folderId: 123, title: 'article' }] as Feed[]

			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0].feeds.length).toEqual(1)
			expect(state.folders[0].feeds[0].id).toEqual(345)
		})

		it('SET_FEEDS should update the folder unreadCount with the feed unreadCount', () => {
			const state = { folders: [{ name: 'test', id: 123, unreadCount: 0 }] as Folder[] } as AppState
			const feeds = [{ id: 345, folderId: 123, title: 'article', unreadCount: 5 }] as Feed[]

			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0].feedCount).toEqual(5)
		})

		it('SET_FEEDS should update the folder updateErrorCount with the feed updateErrorCount when greater than eight', () => {
			const state = { folders: [{ name: 'test', id: 123, feedCount: 0 }] as Folder[] } as AppState
			const feeds = [{ id: 345, folderId: 123, title: 'article', updateErrorCount: 9 }] as Feed[]

			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0].updateErrorCount).toEqual(9)
		})

		it('ADD_FEED should add the feed to the folder in the state and update counters', () => {
			const state = { folders: [{ name: 'test', id: 123, feeds: [] as Feed[], feedCount: 0, updateErrorCount: 0 }] as Folder[] } as AppState
			const feed = { id: 345, folderId: 123, title: 'article', unreadCount: 5, updateErrorCount: 9 } as Feed

			mutations[FEED_MUTATION_TYPES.ADD_FEED](state, feed)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0].feeds.length).toEqual(1)
			expect(state.folders[0].feeds[0].id).toEqual(345)
			expect(state.folders[0].feedCount).toEqual(5)
			expect(state.folders[0].updateErrorCount).toEqual(9)
		})

		it('UPDATE_FOLDER should update the folder  properties in the state', () => {
			const state = { folders: [{ name: 'test', id: 123 }] as Folder[] } as AppState
			const newFolder = { id: 123, name: 'newName' }

			mutations[FOLDER_MUTATION_TYPES.UPDATE_FOLDER](state, newFolder)
			expect(state.folders[0].name).toEqual('newName')
		})

		it('MODIFY_FOLDER_UNREAD_COUNT should update the folder feedCount in the state based on the delta', () => {
			const state = { folders: [{ name: 'test', id: 123, feedCount: 10 }] as Folder[] } as AppState

			mutations[FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT](state, { folderId: 123, delta: -3 })
			expect(state.folders[0].feedCount).toEqual(7)
		})

		it('SET_FEED_ALL_READ should update the folder feedCount in the state based on the feed unreadCount', () => {
			const state = { folders: [{ name: 'test', id: 123, feedCount: 10 }] as Folder[] } as AppState

			mutations[FEED_MUTATION_TYPES.SET_FEED_ALL_READ](state, { id: 1, folderId: 123, unreadCount: 2 })
			expect(state.folders[0].feedCount).toEqual(8)
		})
	})
})
