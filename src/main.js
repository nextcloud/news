import Vue from 'vue'
import Vuex from 'vuex'
import App from './App'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(Vuex)

export default new Vue({
	el: '#content',
	render: h => h(App),
})
