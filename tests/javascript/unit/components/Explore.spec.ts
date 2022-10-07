import Explore from 'Components/Explore.vue'
import sinon from 'sinon';
import axios from '@nextcloud/axios'
import * as router from '@nextcloud/router'

import { store, localVue } from '../setupStore'

import { shallowMount } from '@vue/test-utils'

describe('Explore.vue', () => {
	'use strict'

	it('should initialize without showing AddFeed Component', () => {
    sinon.stub(axios, 'get').resolves({ data: { } });
    sinon.stub(router, 'generateUrl').returns('');
    
		const wrapper = shallowMount(Explore, { localVue, store })

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy
	});
});