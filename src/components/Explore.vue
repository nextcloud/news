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

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import axios from '@nextcloud/axios'
import AddFeed from './AddFeed.vue'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'ExploreComponent',
	components: {
		NcButton,
		AddFeed,
	},
	data() {
		return {
			exploreSites: [],
			feed: {},
			showAddFeed: false,
		}
	},
	created() {
		this.sites()
	},

	methods: {
		async sites() {
			const settings = await axios.get(generateUrl('/apps/news/settings'))

			const exploreUrl = settings.data.settings.exploreUrl + 'feeds.en.json'
			const explore = await axios.get(exploreUrl)

			Object.keys(explore.data).forEach((key) =>
				explore.data[key].forEach((value) =>
					this.exploreSites.push(value),
				),
			)
		},
		async subscribe(feed) {
			this.feed = feed
			this.showAddFeed = true
		},
		closeShowAddFeed() {
			this.showAddFeed = false
		},
	},
}
</script>
