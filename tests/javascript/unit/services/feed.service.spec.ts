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
})
