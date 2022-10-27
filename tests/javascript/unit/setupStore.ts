// NOTE: This was copied from nextcloud/tasks repo
import { createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'

const localVue = createLocalVue()
localVue.use(Vuex)

const store = new Vuex.Store({
	modules: {
	},
})
export { store, localVue }
