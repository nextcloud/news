import axios from '@nextcloud/axios'
import { beforeEach, describe, expect, it } from 'vitest'
import { ITEM_TYPES, ItemService } from '../../../../src/dataservices/item.service'

describe('item.service.ts', () => {
	'use strict'

	beforeEach(() => {
		axios.get.mockReset()
		axios.post.mockReset()
	})

	describe('fetchAll', () => {
		it('should call GET with offset set to start param, ALL item type', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchAll(0)

			expect(axios.get).toBeCalled()
			const queryParams = axios.get.mock.calls[0][1].params

			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.ALL)
		})
	})

	describe('fetchStarred', () => {
		it('should call GET with offset set to start param and STARRED item type', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchStarred(0)

			expect(axios.get).toBeCalled()
			const queryParams = axios.get.mock.calls[0][1].params

			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.STARRED)
		})
	})

	describe('fetchUnread', () => {
		it('should call GET with offset set to start param and UNREAD item type', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchUnread(2)

			expect(axios.get).toBeCalled()
			const queryParams = axios.get.mock.calls[0][1].params

			expect(queryParams.offset).toEqual(2)
			expect(queryParams.type).toEqual(ITEM_TYPES.UNREAD)
		})
	})

	describe('fetchFeedItems', () => {
		it('should call GET with offset set to start param, FEED item type, and id set to feedId', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchFeedItems(123, 0)

			expect(axios.get).toBeCalled()
			const queryParams = axios.get.mock.calls[0][1].params

			expect(queryParams.id).toEqual(123)
			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.FEED)
		})
	})

	describe('fetchFolderItems', () => {
		it('should call GET with offset set to start param, FOLDER item type, and id set to folderId', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchFolderItems(123, 0)

			expect(axios.get).toBeCalled()
			const queryParams = axios.get.mock.calls[0][1].params

			expect(queryParams.id).toEqual(123)
			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.FOLDER)
		})
	})

	describe('markRead', () => {
		it('should call POST with item id in URL and read param', async () => {
			await ItemService.markRead({ id: 123 } as any, true)

			expect(axios.post).toBeCalled()
			const args = axios.post.mock.calls[0]

			expect(args[0]).toContain('123')
			expect(args[1].isRead).toEqual(true)
		})
	})

	describe('markStarred', () => {
		it('should call POST with item feedId and guidHash in URL and read param', async () => {
			await ItemService.markStarred({ feedId: 1, guidHash: 'abc' } as any, false)

			expect(axios.post).toBeCalled()
			const args = axios.post.mock.calls[0]

			expect(args[0]).toContain('1')
			expect(args[0]).toContain('abc')
			expect(args[1].isStarred).toEqual(false)
		})
	})
})
