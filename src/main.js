import Vue from 'vue'
import Vuex from 'vuex'
import App from './App'
import VueRouter from 'vue-router'
import Explore from './components/Explore'

import {generateUrl} from "@nextcloud/router";

import store from './store/index.js'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(Vuex)
Vue.use(VueRouter)

import { Tooltip } from '@nextcloud/vue'

Vue.directive('tooltip', Tooltip)


const routes = [
    {
        name: 'explore',
        path: '#explore',
        component: Explore
    },
]

const router = new VueRouter({
    mode: 'history',
    base: generateUrl('apps/news'),
    routes
});


export default new Vue({
	el: '#content',
	store: new Vuex.Store(store),
	router,
	render: h => h(App),
})
