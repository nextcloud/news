import { ITEM_TYPES, ItemService } from '../../../../src/dataservices/item.service'
import axios from '@nextcloud/axios'

jest.mock('@nextcloud/axios')

describe('item.service.ts', () => {
	'use strict'

	beforeEach(() => {
		(axios.get as any).mockReset();
		(axios.post as any).mockReset()
	})

	describe('fetchAll', () => {
		it('should call GET with offset set to start param, ALL item type', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchAll(0)

			expect(axios.get).toBeCalled()
			const queryParams = (axios.get as any).mock.calls[0][1].params

			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.ALL)
		})
	})

	describe('fetchStarred', () => {
		it('should call GET with offset set to start param and STARRED item type', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchStarred(0)

			expect(axios.get).toBeCalled()
			const queryParams = (axios.get as any).mock.calls[0][1].params

			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.STARRED)
		})
	})

	describe('fetchUnread', () => {
		it('should call GET with offset set to start param and UNREAD item type', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchUnread(2)

			expect(axios.get).toBeCalled()
			const queryParams = (axios.get as any).mock.calls[0][1].params

			expect(queryParams.offset).toEqual(2)
			expect(queryParams.type).toEqual(ITEM_TYPES.UNREAD)
		})
	})

	describe('fetchFeedItems', () => {
		it('should call GET with offset set to start param, FEED item type, and id set to feedId', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchFeedItems(123, 0)

			expect(axios.get).toBeCalled()
			const queryParams = (axios.get as any).mock.calls[0][1].params

			expect(queryParams.id).toEqual(123)
			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.FEED)
		})
	})

	describe('fetchFolderItems', () => {
		it('should call GET with offset set to start param, FOLDER item type, and id set to folderId', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ItemService.fetchFolderItems(123, 0)

			expect(axios.get).toBeCalled()
			const queryParams = (axios.get as any).mock.calls[0][1].params

			expect(queryParams.id).toEqual(123)
			expect(queryParams.offset).toEqual(0)
			expect(queryParams.type).toEqual(ITEM_TYPES.FOLDER)
		})
	})

	describe('markRead', () => {
		it('should call POST with item id in URL and read param', async () => {
			await ItemService.markRead({ id: 123 } as any, true)

			expect(axios.post).toBeCalled()
			const args = (axios.post as any).mock.calls[0]

			expect(args[0]).toContain('123')
			expect(args[1].isRead).toEqual(true)
		})
	})

	describe('markStarred', () => {
		it('should call POST with item feedId and guidHash in URL and read param', async () => {
			await ItemService.markStarred({ feedId: 1, guidHash: 'abc' } as any, false)

			expect(axios.post).toBeCalled()
			const args = (axios.post as any).mock.calls[0]

			expect(args[0]).toContain('1')
			expect(args[0]).toContain('abc')
			expect(args[1].isStarred).toEqual(false)
		})
	})
})
