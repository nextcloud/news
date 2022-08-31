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
				<NcButton @click="subscribe(entry.feed)"
					type="primary"
					class="grid-item__button"
					:wide="true">
					{{ t("news", "Subscribe to {title}", {title: entry.title}) }}
				</NcButton>
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

<style lang="scss" scoped>
.grid-container {
  padding: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  /* This is better for small screens, once min() is better supported */
  /* grid-template-columns: repeat(auto-fill, minmax(min(200px, 100%), 1fr)); */
  gap: 1rem;

	.grid-item {
		border: 2px solid var(--color-border);
		border-radius: var(--border-radius-large);
		padding: 1rem;
		display: flex;
		flex-direction: column;

		&__button {
			margin-top: auto;
		}
	}
  .grid-item .explore-title {
    background-repeat: no-repeat;
    background-position: 0 center;
    background-size: 24px;
    padding-left: 32px;
  }
  .grid-item .explore-title a {
    word-wrap: break-word;
  }
  .grid-item .explore-title a:hover, #explore .grid-item .explore-title a:focus {
    text-decoration: underline;
  }
  .grid-item .explore-logo {
    text-align: center;
    margin-top: 25px;
  }
  .grid-item .explore-logo img {
    width: 100%;
  }
  .grid-item .explore-subscribe {
    margin-top: 16px;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
