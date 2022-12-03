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
				<NcButton @click="subscribe(entry.feed)">
					{{ t("news", "Subscribe to") }} {{ entry.title }}
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script lang="ts">

import Vue from 'vue'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import axios from '@nextcloud/axios'
import * as router from '@nextcloud/router'

import AddFeed from './AddFeed.vue'

import { ExploreSite } from '../types/ExploreSite'
import { Feed } from '../types/Feed'

const ExploreComponent = Vue.extend({
	components: {
		NcButton,
		AddFeed,
	},
	data: () => {
		const exploreSites: ExploreSite[] = []
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
			const explore = await axios.get(exploreUrl)

			Object.keys(explore.data).forEach((key) =>
				explore.data[key].forEach((value: ExploreSite) =>
					this.exploreSites.push(value),
				),
			)
		},
		async subscribe(feed: Feed) {
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
