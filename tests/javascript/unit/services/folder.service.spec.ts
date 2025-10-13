import axios from '@nextcloud/axios'
import { beforeEach, describe, expect, it } from 'vitest'
import { FolderService } from './../../../../src/dataservices/folder.service'

describe('folder.service.ts', () => {
	'use strict'

	beforeEach(() => {
		axios.get.mockReset()
		axios.post.mockReset()
		axios.delete.mockReset()
	})

	describe('fetchAllFolders', () => {
		it('should call GET to retrieve all folders', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await FolderService.fetchAllFolders()

			expect(axios.get).toBeCalled()
		})
	})

	describe('createFolder', () => {
		it('should call POST with folderName param', async () => {
			await FolderService.createFolder({ name: 'abc' })

			expect(axios.post).toBeCalled()
			const args = axios.post.mock.calls[0]

			expect(args[1].folderName).toEqual('abc')
		})
	})

	describe('renameFolder', () => {
		it('should call POST with item id in URL and folderName param', async () => {
			await FolderService.renameFolder({ id: 123, name: 'abc' })

			expect(axios.post).toBeCalled()
			const args = axios.post.mock.calls[0]

			expect(args[0]).toContain('123/rename')
			expect(args[1].folderName).toEqual('abc')
		})
	})

	describe('deleteFolder', () => {
		it('should call POST with item id in URL and read param', async () => {
			await FolderService.deleteFolder({ id: 123 })

			expect(axios.delete).toBeCalled()
			const args = axios.delete.mock.calls[0]

			expect(args[0]).toContain('123')
		})
	})
})
