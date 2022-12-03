import axios from '@nextcloud/axios'
import { Folder } from '../../../../src/types/Folder'
import { AppState } from '../../../../src/store'
import { FOLDER_ACTION_TYPES, FOLDER_MUTATION_TYPES, mutations, actions } from '../../../../src/store/folder'

jest.mock('@nextcloud/axios')

describe('folder.ts', () => {
	'use strict'

	describe('actions', () => {
		it('FETCH_FOLDERS', () => {
			// TODO
		})

		it('ADD_FOLDERS should send POST and then commit the folders returned', async () => {
			(axios.post as any).mockResolvedValue({ data: { folders: [] } })

			const folder = {} as Folder
			const commit = jest.fn()
		  await actions[FOLDER_ACTION_TYPES.ADD_FOLDERS]({ commit }, { folder })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})

		it('DELETE_FOLDER', () => {
			// TODO
		})
	})

	describe('mutations', () => {
		it('SET_FOLDERS', () => {
			const state = { folders: [] as Folder[] } as AppState
			const folders = [] as Folder[]
			(mutations[FOLDER_MUTATION_TYPES.SET_FOLDERS] as any)(state, folders)

			expect(state.folders.length).toEqual(0)
		})

		it('DELETE_FOLDER', () => {
			// TODO
		})
	})
})
