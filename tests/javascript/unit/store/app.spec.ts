import { AppInfoState, mutations } from '../../../../src/store/app'
import { APPLICATION_MUTATION_TYPES } from '../../../../src/types/MutationTypes'

jest.mock('@nextcloud/router')

describe('app.ts', () => {
	'use strict'

	// describe('actions', () => {

	// })

	describe('mutations', () => {
		it('SET_ERROR should update the error in the state', () => {
			const state = { error: undefined } as AppInfoState

			const error = { message: 'test err' };

			(mutations[APPLICATION_MUTATION_TYPES.SET_ERROR] as any)(state, error)
			expect(state.error).toEqual(error);

			(mutations[APPLICATION_MUTATION_TYPES.SET_ERROR] as any)(state, undefined)
			expect(state.error).toEqual(undefined)
		})
	})
})
