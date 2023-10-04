import { ShareService } from './../../../../src/dataservices/share.service'
import axios from '@nextcloud/axios'

jest.mock('@nextcloud/axios')

describe('share.service.ts', () => {
	'use strict'

	beforeEach(() => {
		(axios.get as any).mockReset();
		(axios.post as any).mockReset()
	})

	describe('fetchUsers', () => {
		it('should call GET to retrieve users', async () => {
			(axios as any).get.mockResolvedValue({ data: { feeds: [] } })

			await ShareService.fetchUsers('abc')

			expect(axios.get).toBeCalled()
			const args = (axios.get as any).mock.calls[0]

			expect(args[0]).toContain('search=abc')
		})
	})

	describe('share', () => {
		it('should call POST for each user passed', async () => {
			await ShareService.share(123, ['share-user'])

			expect(axios.post).toBeCalledTimes(1)
			let args = (axios.post as any).mock.calls[0]

			expect(args[0]).toContain('123/share/share-user')

			await ShareService.share(345, ['share-user', 'share2'])

			expect(axios.post).toBeCalledTimes(3)

			args = (axios.post as any).mock.calls[1]
			expect(args[0]).toContain('345/share/share-user')
			args = (axios.post as any).mock.calls[2]
			expect(args[0]).toContain('345/share/share2')
		})
	})
})
