<template>
    <div id="explore">
        <AddFeed v-if="showAddFeed" :feed="feed" @close="closeShowAddFeed()" />
        <div class="grid-container">
            <div v-for="entry in exploreSites"
                :key="entry.title"
                class="explore-feed grid-item">
                <h2 v-if="entry.favicon"
                    class="explore-title"
                    :style="{ backgroundImage: 'url(' + entry.favicon + ')' }">
                    <a target="_blank" rel="noreferrer" :href="entry.url">
                        {{ entry.title }}
                    </a>
                </h2>
                <h2 v-if="!entry.favicon" class="icon-rss explore-title">
                    {{ entry.title }}
                </h2>
                <div class="explore-content">
                    <p>{{ entry.description }}</p>

                    <div class="explore-logo">
                        <img :src="entry.image">
                    </div>
                </div>
                <Button @click="subscribe(entry.feed)">
                    {{ t("news", "Subscribe to") }} {{ entry.title }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Button from '@nextcloud/vue/dist/Components/Button'
import axios from '@nextcloud/axios'
import AddFeed from './AddFeed.vue'
import { generateUrl } from '@nextcloud/router'
import { Component, Vue } from 'vue-property-decorator'

@Component({
    components: {
        Button,
        AddFeed,
    },
})
class Explore extends Vue {

    public exploreSites: any[] = [{ title: 'TEST3', description: 'test' }]
    public feed: any = {}
    public showAddFeed = false

    constructor() {
        super()
        this.sites()
    }

    async sites() {
        const settings = await axios.get(generateUrl('/apps/news/settings'))

        const exploreUrl = settings.data.settings.exploreUrl + 'feeds.en.json'
        const explore = await axios.get(exploreUrl)

        Object.keys(explore.data).forEach((key) =>
            explore.data[key].forEach((value: any) =>
                this.exploreSites.push(value),
            ),
        )
    }

    async subscribe(feed: any) {
        this.feed = feed
        this.showAddFeed = true
    }

    closeShowAddFeed() {
        this.showAddFeed = false
    }

}

export default Explore
</script>
