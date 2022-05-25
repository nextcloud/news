<template>
	<div id="explore">
		<AddFeed v-if="showAddFeed" :feed="feed" @close="closeShowAddFeed()" />
		<div class="grid-container">
			<div
				v-for="entry in explorableSites"
				:key="entry.title"
				class="explore-feed grid-item">
				<h2
					v-if="entry.favicon"
					class="explore-title"
					:style="{ backgroundImage: 'url(' + entry.favicon + ')' }">
					<a target="_blank" rel="noreferrer" :href="entry.url">{{
						entry.title
					}}</a>
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
/* eslint-disable no-console */
/* eslint-disable vue/require-prop-type-constructor */
/* eslint-disable vue/require-default-prop */
/* eslint-disable vue/no-mutating-props */

// import Modal from '@nextcloud/vue/dist/Components/Modal'
import Button from '@nextcloud/vue/dist/Components/Button'
import axios from '@nextcloud/axios'
import AddFeed from './AddFeed'
import { generateUrl } from '@nextcloud/router'

export default {
	components: {
		// Modal,
		Button,
		AddFeed,
	},
	props: {
		explorableSites: {
			type: Array,
			default: () => [],
			required: true,
		},
		showAddFeed: false,
		feed: '',
	},
	created() {
		this.sites()
	},
	methods: {
		async sites() {
			const settings = await axios.get(
				generateUrl('/apps/news/settings')
			)
			console.log(settings.data)
			console.log(settings.data.settings.exploreUrl)

			const exploreUrl
                = settings.data.settings.exploreUrl + 'feeds.en.json'
			const explore = await axios.get(exploreUrl)
			console.log(explore.data)

			Object.keys(explore.data).forEach((key) =>
				explore.data[key].forEach((value) =>
					this.explorableSites.push(value)
				)
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
