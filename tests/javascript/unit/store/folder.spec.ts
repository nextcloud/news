import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { Folder } from '../../../../src/types/Folder'
import { AppState } from '../../../../src/store'
import { FOLDER_ACTION_TYPES, mutations, actions } from '../../../../src/store/folder'
import { FOLDER_MUTATION_TYPES } from '../../../../src/types/MutationTypes'

jest.mock('@nextcloud/axios')
jest.mock('@nextcloud/router')

describe('folder.ts', () => {
	'use strict'

	describe('actions', () => {
		it('FETCH_FOLDERS should send GET and then commit folders returned to state', async () => {
			(generateUrl as any).mockReturnValue('');
			(axios.get as any).mockResolvedValue({ data: { folders: [] } })

			const commit = jest.fn()

		  await (actions[FOLDER_ACTION_TYPES.FETCH_FOLDERS] as any)({ commit })
			expect(axios.get).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('ADD_FOLDERS should send POST and then commit the folders returned to state', async () => {
			(axios.post as any).mockResolvedValue({ data: { folders: [] } })

			const folder = {} as Folder
			const commit = jest.fn()

		  await actions[FOLDER_ACTION_TYPES.ADD_FOLDERS]({ commit }, { folder })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('DELETE_FOLDER should send DELETE and then commit deleted folder to state', async () => {
			(axios.delete as any).mockResolvedValue()

			const folder = {} as Folder
			const commit = jest.fn()

		  await actions[FOLDER_ACTION_TYPES.DELETE_FOLDER]({ commit }, { folder })
			expect(axios.delete).toBeCalled()
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
