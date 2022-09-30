
import { createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'
// import router from '@/router.js'

const localVue = createLocalVue()
localVue.use(Vuex)

const store = new Vuex.Store({
	modules: {
	},
})
export { store, localVue }
