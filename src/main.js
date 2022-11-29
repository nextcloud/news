
import Vue from 'vue'
import App from './App.vue'
import VueRouter from 'vue-router'
import Explore from './components/Explore.vue'
import { generateUrl } from '@nextcloud/router'
import Vuex, { Store } from 'vuex'

import mainStore from './store'

import { Tooltip } from '@nextcloud/vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(Vuex)
Vue.use(VueRouter)

Vue.directive('tooltip', Tooltip)

const routes = [
	{
		name: 'explore',
		path: '#explore',
		component: Explore,
	},
]

const router = new VueRouter({
	mode: 'history',
	base: generateUrl('apps/news'),
	routes,
})

const store = new Store(mainStore)

export default new Vue({
	router,
	store,
	el: '#content',
	render: (h) => h(App),
})
