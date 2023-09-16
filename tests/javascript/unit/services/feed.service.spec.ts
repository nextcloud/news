import { FeedService } from './../../../../src/dataservices/feed.service'
import axios from '@nextcloud/axios'

jest.mock('@nextcloud/axios')

describe('feed.service.ts', () => {
	'use strict'

	beforeEach(() => {
		(axios.get as any).mockReset();
		(axios.post as any).mockReset()
	})

	describe('fetchAllFeeds', () => {
		it('should call GET to retrieve all feeds', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await FeedService.fetchAllFeeds()

			expect(axios.get).toBeCalled()
		})
	})

	describe('addFeed', () => {
		it('should call POST with item id in URL and read param', async () => {
			await FeedService.addFeed({ url: 'http://example.com', folderId: 0 })

			expect(axios.post).toBeCalled()
			const args = (axios.post as any).mock.calls[0]

			expect(args[1].url).toEqual('http://example.com')
		})
	})

	describe('markRead', () => {
		it('should call POST with item id + read in URL and highestItemId param', async () => {
			await FeedService.markRead({ feedId: 1, highestItemId: 234 })

			expect(axios.post).toBeCalled()
			const args = (axios.post as any).mock.calls[0]

			expect(args[0]).toContain('1/read')
			expect(args[1].highestItemId).toEqual(234)
		})
	})

	describe('updateFeed', () => {
		it('should call PATCH with item id in URL and title param', async () => {
			await FeedService.updateFeed({ feedId: 1, title: 'abc' })

			expect(axios.patch).toBeCalled()
			const args = (axios.patch as any).mock.calls[0]

			expect(args[0]).toContain('1')
			expect(args[1].title).toEqual('abc')
		})
	})

	describe('deleteFeed', () => {
		it('should call DELETE with item id in URL ', async () => {
			await FeedService.deleteFeed({ feedId: 1 })

			expect(axios.delete).toBeCalled()
			const args = (axios.delete as any).mock.calls[0]

			expect(args[0]).toContain('1')
		})
	})
})
