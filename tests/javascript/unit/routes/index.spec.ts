import { beforeEach, describe, expect, it } from 'vitest'
import { getInitialRoute, ROUTES } from '../../../../src/routes/index.ts'
import app from '../../../../src/store/app.ts'

describe('getInitialRoute', () => {
	beforeEach(() => {
		app.state.lastViewedFeedType = ''
		app.state.lastViewedFeedId = 0
	})

	it('should return FEED route with feedId if lastViewedFeedType is 0', () => {
		app.state.lastViewedFeedType = '0'
		app.state.lastViewedFeedId = 123

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.FEED,
			params: { feedId: 123 },
		})
	})

	it('should return FOLDER route with folderId if lastViewedFeedType is 1', () => {
		app.state.lastViewedFeedType = '1'
		app.state.lastViewedFeedId = 456

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.FOLDER,
			params: { folderId: 456 },
		})
	})

	it('should return STARRED route with starredFeedId if lastViewedFeedType is 2 and lastViewedFeedId > 0', () => {
		app.state.lastViewedFeedType = '2'
		app.state.lastViewedFeedId = 789

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.STARRED,
			params: { starredFeedId: 789 },
		})
	})

	it('should return STARRED route if lastViewedFeedType is 2', () => {
		app.state.lastViewedFeedType = '2'
		app.state.lastViewedFeedId = 0

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.STARRED,
			params: {},
		})
	})

	it('should return ALL route if lastViewedFeedType is 3', () => {
		app.state.lastViewedFeedType = '3'

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.ALL,
		})
	})

	it('should return EXPLORE route if lastViewedFeedType is 5', () => {
		app.state.lastViewedFeedType = '5'

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.EXPLORE,
		})
	})

	it('should return UNREAD route for any other type', () => {
		app.state.lastViewedFeedType = 'unknown'

		const result = getInitialRoute()
		expect(result).toEqual({
			name: ROUTES.UNREAD,
		})
	})
})
