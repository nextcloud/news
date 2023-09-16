import { Folder } from '../../../../src/types/Folder'
import { AppState } from '../../../../src/store'
import { FOLDER_ACTION_TYPES, mutations, actions } from '../../../../src/store/folder'
import { FEED_MUTATION_TYPES, FOLDER_MUTATION_TYPES } from '../../../../src/types/MutationTypes'
import { FolderService } from '../../../../src/dataservices/folder.service'

jest.mock('@nextcloud/router')

describe('folder.ts', () => {
	'use strict'

	describe('actions', () => {
		it('FETCH_FOLDERS should call FolderService.fetchAllFolders and then commit folders returned to state', async () => {
			FolderService.fetchAllFolders = jest.fn();
			(FolderService.fetchAllFolders as any).mockResolvedValue({ data: { folders: [] } })

			const commit = jest.fn()

		  await (actions[FOLDER_ACTION_TYPES.FETCH_FOLDERS] as any)({ commit })
			expect(FolderService.fetchAllFolders).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('ADD_FOLDERS should call FolderService.createFolder and then commit the folders returned to state', async () => {
			FolderService.createFolder = jest.fn();
			(FolderService.createFolder as any).mockResolvedValue({ data: { folders: [] } })

			const folder = {} as Folder
			const commit = jest.fn()

		  await actions[FOLDER_ACTION_TYPES.ADD_FOLDERS]({ commit } as any, { folder })
			expect(FolderService.createFolder).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('DELETE_FOLDER should call FolderService.deleteFolder and then commit deleted folder to state', async () => {
			FolderService.deleteFolder = jest.fn();
			(FolderService.deleteFolder as any).mockResolvedValue()

			const folder = {} as Folder
			const commit = jest.fn()

		  await actions[FOLDER_ACTION_TYPES.DELETE_FOLDER]({ commit } as any, { folder })
			expect(FolderService.deleteFolder).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('FOLDER_SET_NAME should call FolderService.renameFolder and then commit deleted folder to state', async () => {
			FolderService.renameFolder = jest.fn();
			(FolderService.renameFolder as any).mockResolvedValue()

			const folder = {} as Folder
			const commit = jest.fn()

		  await actions[FOLDER_ACTION_TYPES.FOLDER_SET_NAME]({ commit } as any, { folder, name: 'newName' } as any)
			expect(FolderService.renameFolder).toBeCalledWith({ id: folder.id, name: 'newName' })
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		it('SET_FOLDERS should add the passed in folders to the state', () => {
			const state = { folders: [] as Folder[] } as AppState
			let folders = [] as Folder[]

			(mutations[FOLDER_MUTATION_TYPES.SET_FOLDERS] as any)(state, folders)
			expect(state.folders.length).toEqual(0)

			folders = [{ name: 'test' }] as Folder[]

			(mutations[FOLDER_MUTATION_TYPES.SET_FOLDERS] as any)(state, folders)
			expect(state.folders.length).toEqual(1)
			expect(state.folders[0]).toEqual(folders[0])
		})

		it('DELETE_FOLDER should remove the passed in folder from the state', () => {
			const state = { folders: [{ name: 'test' }] as Folder[] } as AppState
			const folders = [state.folders[0]] as Folder[]

			(mutations[FOLDER_MUTATION_TYPES.DELETE_FOLDER] as any)(state, folders)
			expect(state.folders.length).toEqual(0)
		})

		it('UPDATE_FOLDER should update the folder  properties in the state', () => {
			const state = { folders: [{ name: 'test', id: 123 }] as Folder[] } as AppState
			const newFolder = { id: 123, name: 'newName' };

			(mutations[FOLDER_MUTATION_TYPES.UPDATE_FOLDER] as any)(state, newFolder)
			expect(state.folders[0].name).toEqual('newName')
		})

		it('MODIFY_FOLDER_UNREAD_COUNT should update the folder feedCount in the state based on the delta', () => {
			const state = { folders: [{ name: 'test', id: 123, feedCount: 10 }] as Folder[] } as AppState

			(mutations[FOLDER_MUTATION_TYPES.MODIFY_FOLDER_UNREAD_COUNT] as any)(state, { folderId: 123, delta: -3 })
			expect(state.folders[0].feedCount).toEqual(7)
		})

		it('SET_FEED_ALL_READ should update the folder feedCount in the state based on the feed unreadCount', () => {
			const state = { folders: [{ name: 'test', id: 123, feedCount: 10 }] as Folder[] } as AppState

			(mutations[FEED_MUTATION_TYPES.SET_FEED_ALL_READ] as any)(state, { id: 1, folderId: 123, unreadCount: 2 })
			expect(state.folders[0].feedCount).toEqual(8)
		})
	})
})
