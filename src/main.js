import Vue from 'vue'
import App from './App'
import VueRouter from 'vue-router'
import Explore from './components/Explore'
import {generateUrl} from "@nextcloud/router";

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(VueRouter)

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
})

export default new Vue({
    router,
    el: '#content',
    render: h => h(App),
})
