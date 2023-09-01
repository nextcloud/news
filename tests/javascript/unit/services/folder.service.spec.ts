import { FolderService } from './../../../../src/dataservices/folder.service'
import axios from '@nextcloud/axios'

jest.mock('@nextcloud/axios')

describe('folder.service.ts', () => {
	'use strict'

	beforeEach(() => {
		(axios.get as any).mockReset();
		(axios.post as any).mockReset()
	})

	describe('fetchAllFolders', () => {
		it('should call GET to retrieve all folders', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await FolderService.fetchAllFolders()

			expect(axios.get).toBeCalled()
		})
	})

	describe('createFolder', () => {
		it('should call POST with item id in URL and read param', async () => {
			await FolderService.createFolder({ name: 'abc' })

			expect(axios.post).toBeCalled()
			const args = (axios.post as any).mock.calls[0]

			expect(args[1].folderName).toEqual('abc')
		})
	})

	describe('deleteFolder', () => {
		it('should call POST with item id in URL and read param', async () => {
			await FolderService.deleteFolder({ id: 123 })

			expect(axios.delete).toBeCalled()
			const args = (axios.delete as any).mock.calls[0]

			expect(args[0]).toContain('123')
		})
	})
})
