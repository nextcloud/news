<template>
	<NcAppContent>
		<div id="explore">
			<AddFeed v-if="showAddFeed" :feed="feed" @close="closeShowAddFeed()" />
			<div v-if="!exploreSites" style="margin: auto;">
				{{ t('news', 'No feeds found to add') }}
			</div>
			<div v-else class="grid-container">
				<div v-for="entry in exploreSites"
					:key="entry.title"
					class="explore-feed grid-item">
					<h2 v-if="entry.favicon"
						class="explore-title icon"
						:style="{ backgroundImage: 'url(' + entry.favicon + ')' }">
						<a target="_blank" rel="noreferrer" :href="entry.url">
							{{ entry.title }}
						</a>
					</h2>
					<h2 v-if="!entry.favicon" class="icon-rss explore-title">
						{{ entry.title }}
					</h2>
					<div class="explore-content" style="flex-grow: 1">
						<p>{{ entry.description }}</p>

						<div v-if="entry.image" class="explore-logo">
							<img :src="entry.image">
						</div>
					</div>
					<NcButton style="max-width: 100%;" @click="subscribe(entry)">
						{{ t("news", "Subscribe to") }} {{ entry.title }}
					</NcButton>
				</div>
			</div>
		</div>
	</NcAppContent>
</template>

<script lang="ts">

import Vue from 'vue'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import axios from '@nextcloud/axios'
import * as router from '@nextcloud/router'

import AddFeed from '../AddFeed.vue'

import { ExploreSite } from '../../types/ExploreSite'
import { Feed } from '../../types/Feed'

const ExploreComponent = Vue.extend({
	components: {
		NcAppContent,
		NcButton,
		AddFeed,
	},
	data: (): {
		exploreSites: ExploreSite[] | undefined;
		feed: Feed;
		showAddFeed: boolean;
	} => {
		const exploreSites: ExploreSite[] | undefined = []
		const feed: Feed = {} as Feed
		const showAddFeed = false

		return {
			exploreSites,
			feed,
			showAddFeed,
		}
	},
	async created() {
		await this.sites()
	},

	methods: {
		async sites() {
			const settings = await axios.get(router.generateUrl('/apps/news/settings'))

			const exploreUrl = settings.data.settings?.exploreUrl + 'feeds.en.json'
			try {
				const explore = await axios.get(exploreUrl)

				Object.keys(explore.data).forEach((key) =>
					explore.data[key].forEach((value: ExploreSite) => {
						if (this.exploreSites) {
							this.exploreSites.push(value)
						} else {
							this.exploreSites = [value]
						}
					},
					),
				)
			} catch {
				this.exploreSites = undefined
			}

		},
		subscribe(feed: Feed) {
			this.feed = feed
			this.showAddFeed = true
		},
		closeShowAddFeed() {
			this.showAddFeed = false
		},
	},
})

export default ExploreComponent

</script>

<style scoped>
#explore {
    height: 100%;
    width: 100%;
    padding: 45px 0 45px 45px;
		display: flex;
		justify-items: center;
}

#explore .grid-container {
	display:flex;
	flex-wrap: wrap;
}

#explore .grid-item {
    width: 300px;
    border: 2px solid var(--color-border);
    border-radius: var(--border-radius-large);
    margin: 0 24px 24px 0;
    padding: 24px;
		flex-grow: 1;
		max-width: calc(50% - 24px);
		display: flex;
		flex-direction: column;
}

#explore .grid-item .explore-title {
    background-repeat: no-repeat;
    background-position: 0 center;
    background-size: 24px;
}

#explore .grid-item .explore-title.icon {
	padding-left: 32px;
}

#explore .grid-item .explore-title a {
    word-wrap: break-word;
}

#explore .grid-item .explore-title a:hover,
#explore .grid-item .explore-title a:focus {
    text-decoration: underline;
}

#explore .grid-item .explore-logo {
    text-align: center;
    margin-top: 25px;
}

#explore .grid-item .explore-logo img {
    width: 100%;
}

#explore .grid-item .explore-subscribe {
    margin-top: 16px;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#explore .grid-item .explore-content {
	justify-content: center;
  display: flex;
  flex-direction: column;
	margin-bottom: 10px;
}
</style>
