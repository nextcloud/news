import Vue from 'vue'
import NewsFeedWidget from './components/FeedDashboard.vue'

document.addEventListener('DOMContentLoaded', () => {
    console.log("I'm alive")
    OCA.Dashboard.register('news-feed-widget', (el) => {
        console.log("Dashboard registered")
        const View = Vue.extend(NewsFeedWidget)
        new View().$mount(el)
    })
})
