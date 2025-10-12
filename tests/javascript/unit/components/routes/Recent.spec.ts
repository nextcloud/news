import Vuex, { Store } from 'vuex'
import { shallowMount } from '@vue/test-utils'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'

import Recent from '../../../../../src/components/routes/Recent.vue'
import ContentTemplate from '../../../../../src/components/ContentTemplate.vue'

describe('Recent.vue', () => {
	'use strict'
	let wrapper: any

	const mockItems = [
		{
			id: 1,
		}, {
			id: 2,
		}, {
			id: 3,
		}, {
			id: 4,
		}
	]

	const mockItemIds = [ 2, 3 ];

	let store: Store<any>
	beforeAll(() => {
		store = new Vuex.Store({
			state: {
				items: {
					allItems: mockItems,
					recentItemIds: mockItemIds,
				},
			},
			getters: {
				allItems: () => mockItems,
				recentItemIds: () => mockItemIds,
			},
		})

		store.dispatch = vi.fn()
		store.commit = vi.fn()

		wrapper = shallowMount(Recent, {
			global: {
				plugins: [store],
			},
		})
	})

	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('should get recent items from state', () => {
		expect((wrapper.findComponent(ContentTemplate)).props().items.length).toEqual(2)
	})
})
