<template>
	<div id="explore" style="display: flex; justify-items: center;">
		<AddFeed v-if="showAddFeed" :feed="feed" @close="closeShowAddFeed()" />
		<div v-if="!exploreSites" style="margin: auto;">
			{{ t('news', 'No feeds found to add') }}
		</div>
		<div v-else class="grid-container" style="display:flex;flex-wrap: wrap;">
			<div v-for="entry in exploreSites"
				:key="entry.title"
				style="flex-grow: 1; max-width: calc(50% - 24px); min-width: 300px; display: flex; flex-direction: column;"
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
				<div class="explore-content" style="flex-grow: 1">
					<p>{{ entry.description }}</p>

					<div class="explore-logo">
						<img :src="entry.image">
					</div>
				</div>
				<NcButton style="max-width: 100%;" @click="subscribe(entry)">
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

import AddFeed from '../AddFeed.vue'

import { ExploreSite } from '../../types/ExploreSite'
import { Feed } from '../../types/Feed'

const ExploreComponent = Vue.extend({
	components: {
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
