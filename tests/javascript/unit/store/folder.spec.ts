import { Folder } from '../../../../src/types/Folder'
import { AppState } from '../../../../src/store'
import { FOLDER_ACTION_TYPES, mutations, actions } from '../../../../src/store/folder'
import { FOLDER_MUTATION_TYPES } from '../../../../src/types/MutationTypes'
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
	})
})
