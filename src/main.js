import { createApp } from 'vue'
import { createStore } from 'vuex'
import axios from '@nextcloud/axios'

import App from './App.vue'
import router from './routes/index.ts'
import mainStore, { MUTATIONS } from './store/index.ts'

const store = createStore(mainStore)

/**
 * Handles errors returned during application runtime
 *
 * @param {Error} error Error thrown
 * @return {Promise<Error>} Error promise
 */
const handleErrors = function(error) {
	store.commit(MUTATIONS.SET_ERROR, error)
	return Promise.reject(error)
}

/**
 * onSuccessCallback is intentionally undefined (triggers on 2xx responses)
 * Any status codes that falls outside the range of 2xx cause this function to trigger
 */
axios.interceptors.response.use(undefined, handleErrors)

const app = createApp(App)

app.use(store)
app.use(router)

app.config.globalProperties.t = t
app.config.globalProperties.n = n
app.config.globalProperties.OC = OC
app.config.globalProperties.OCA = OCA

app.config.errorHandler = handleErrors

app.mount('#content')

// Make store accessible for setting cron warning (also for plugins in the future)
window.store = store
