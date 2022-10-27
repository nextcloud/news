
import { shallowMount } from '@vue/test-utils'
import { store, localVue } from '../setupStore'
import axios from '@nextcloud/axios'

import * as router from '@nextcloud/router'

import Explore from 'Components/Explore.vue'

describe('Explore.vue', () => {
	'use strict'

	it('should initialize without showing AddFeed Component', () => {
		axios.get = jest.fn().mockResolvedValue({ data: { } })
    (router as any).generateUrl = jest.fn().mockReturnedValue('');
    
		const wrapper = shallowMount(Explore, { localVue, store })

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	});
});