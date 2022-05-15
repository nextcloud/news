import Vue from 'vue'
import App from './App'
import VueRouter from 'vue-router'
import Explore from './components/Explore'
import {generateUrl} from "@nextcloud/router";
import Vuex from 'vuex'
import axios from "@nextcloud/axios";

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(Vuex)
Vue.use(VueRouter)

const feedUrl = generateUrl("/apps/news/feeds")
const folderUrl = generateUrl("/apps/news/folders")

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

const store = new Vuex.Store({
    state: {
        folders: []
    },
    mutations: {
        addFolders(state, folders) {
            folders.forEach(it => {
                state.folders.push(it)
            })
        }
    },
    actions: {
        addFolder({commit},  {folder}) {
            axios.post(folderUrl, {folderName: folder.name}).then(
                response => commit('addFolders', response.data.folders)
            );
        },
        loadFolder({commit}) {
            console.log('loading folders')
            axios.get(folderUrl).then(
                response => commit('addFolders', response.data.folders)
            )
        }
    }
});

export default new Vue({
    router,
    store,
    el: '#content',
    render: h => h(App),
})
