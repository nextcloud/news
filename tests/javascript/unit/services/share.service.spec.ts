import axios from '@nextcloud/axios'
import { beforeEach, describe, expect, it } from 'vitest'
import { ShareService } from './../../../../src/dataservices/share.service'

describe('share.service.ts', () => {
	'use strict'

	beforeEach(() => {
		axios.get.mockReset()
		axios.post.mockReset()
	})

	describe('fetchUsers', () => {
		it('should call GET to retrieve users', async () => {
			axios.get.mockResolvedValue({ data: { feeds: [] } })

			await ShareService.fetchUsers('abc')

			expect(axios.get).toBeCalled()
			const args = axios.get.mock.calls[0]

			expect(args[0]).toContain('search=abc')
		})
	})

	describe('share', () => {
		it('should call POST for each user passed', async () => {
			await ShareService.share(123, ['share-user'])

			expect(axios.post).toBeCalledTimes(1)
			let args = axios.post.mock.calls[0]

			expect(args[0]).toContain('123/share/share-user')

			await ShareService.share(345, ['share-user', 'share2'])

			expect(axios.post).toBeCalledTimes(3)

			args = axios.post.mock.calls[1]
			expect(args[0]).toContain('345/share/share-user')
			args = axios.post.mock.calls[2]
			expect(args[0]).toContain('345/share/share2')
		})
	})
})
