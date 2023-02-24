import Vue from 'vue'
import NewsItemWidget from './components/ItemDashboard.vue'

document.addEventListener('DOMContentLoaded', () => {
    console.log("I'm alive")
    OCA.Dashboard.register('news-item-widget', (el) => {
        console.log("Dashboard registered")
        const View = Vue.extend(NewsItemWidget)
        new View().$mount(el)
    })
})
