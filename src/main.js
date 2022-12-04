
import Vue from 'vue'
import VueRouter from 'vue-router'
import Vuex, { Store } from 'vuex'

import App from './App.vue'
import router from './routes'
import mainStore from './store'

import { Tooltip } from '@nextcloud/vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(Vuex)
Vue.use(VueRouter)

Vue.directive('tooltip', Tooltip)

const store = new Store(mainStore)

export default new Vue({
	router,
	store,
	el: '#content',
	render: (h) => h(App),
})
