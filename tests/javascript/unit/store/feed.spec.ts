import axios from '@nextcloud/axios'
import { Feed } from '../../../../src/types/Feed'
import { AppState } from '../../../../src/store'
import { FEED_ACTION_TYPES, FEED_MUTATION_TYPES, mutations, actions } from '../../../../src/store/feed'

jest.mock('@nextcloud/axios')

describe('feed.ts', () => {
	'use strict'

	describe('actions', () => {
		it('ADD_FEED', async () => {
			(axios as any).post.mockResolvedValue()
			const commit = jest.fn()
			await actions[FEED_ACTION_TYPES.ADD_FEED]({ commit }, { feedReq: { url: '' } })
			expect(axios.post).toBeCalled()
			expect(commit).toBeCalled()
		})
	})

	describe('mutations', () => {
		it('', () => {
			const state = { feeds: [] as Feed[] } as AppState
			const feeds = [] as Feed[]
			mutations[FEED_MUTATION_TYPES.SET_FEEDS](state, feeds)
		})
	})
})
