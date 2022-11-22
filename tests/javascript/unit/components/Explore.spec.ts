import axios from '@nextcloud/axios';
import { shallowMount } from '@vue/test-utils';
import { store, localVue } from '../setupStore';

import * as router from '@nextcloud/router';

import Explore from 'Components/Explore.vue';

jest.mock('@nextcloud/axios');

describe('Explore.vue', () => {
	'use strict';

	it('should initialize without showing AddFeed Component', () => {
		(axios as any).get.mockResolvedValue({ data: {} });
		(router as any).generateUrl = jest.fn().mockReturnValue('');

		jest.fn().mockReturnValue
		const wrapper = shallowMount(Explore, { localVue, store });

		expect(wrapper.vm.$data.showAddFeed).toBeFalsy;
	});
});
