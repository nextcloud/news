<template>
	<NcAppContent>
		<div id="explore">
			<AddFeed v-if="showAddFeed" :feed="feed" @close="closeShowAddFeed()" />
			<div v-if="!exploreSites" style="margin: auto;">
				{{ t('news', 'No feeds found to add') }}
			</div>
			<div v-else class="grid-container">
				<div
					v-for="entry in exploreSites"
					:key="entry.title"
					class="explore-feed grid-item">
					<h2
						v-if="entry.favicon"
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

import type { ExploreSite } from '../../types/ExploreSite.ts'
import type { Feed } from '../../types/Feed.ts'

import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { getLanguage } from '@nextcloud/l10n'
import { defineComponent } from 'vue'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcButton from '@nextcloud/vue/components/NcButton'
import AddFeed from '../AddFeed.vue'

const ExploreComponent = defineComponent({
	name: 'RoutesExplore',
	components: {
		NcAppContent,
		NcButton,
		AddFeed,
	},

	data: (): {
		exploreSites: ExploreSite[] | undefined
		feed: Feed
		showAddFeed: boolean
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
			const customUrl = loadState('news', 'exploreUrl', '')
			const defaultUrl = loadState('news', 'defaultExploreUrl', '')
			const language = getLanguage()

			if (customUrl) {
				// Admin configured custom URL - use language detection + fallback
				await this.fetchFromCustomUrl(customUrl, language)
			} else {
				// Use default URL (backend only has English)
				await this.fetchFromDefaultUrl(defaultUrl)
			}
		},

		async fetchFromCustomUrl(baseUrl: string, language: string) {
			const fileName = `feeds.${language}.json`
			const exploreUrl = baseUrl + (baseUrl.endsWith('/') ? '' : '/') + fileName

			try {
				const explore = await axios.get(exploreUrl)
				this.processExploreData(explore.data)
			} catch {
				// Fallback to English for custom URLs
				if (language !== 'en') {
					try {
						const fallbackUrl = baseUrl + (baseUrl.endsWith('/') ? '' : '/') + 'feeds.en.json'
						const explore = await axios.get(fallbackUrl)
						this.processExploreData(explore.data)
					} catch {
						this.exploreSites = undefined
					}
				} else {
					this.exploreSites = undefined
				}
			}
		},

		async fetchFromDefaultUrl(baseUrl: string) {
			// Default backend only has English feeds
			const exploreUrl = baseUrl + (baseUrl.endsWith('/') ? '' : '/') + 'feeds.en.json'

			try {
				const explore = await axios.get(exploreUrl)
				this.processExploreData(explore.data)
			} catch {
				this.exploreSites = undefined
			}
		},

		processExploreData(data: Record<string, ExploreSite[]>) {
			Object.keys(data).forEach((key) => data[key].forEach((value: ExploreSite) => {
				if (this.exploreSites) {
					this.exploreSites.push(value)
				} else {
					this.exploreSites = [value]
				}
			}))
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
	padding-inline-start: 32px;
}

#explore .grid-item .explore-title a {
	overflow-wrap: break-word;
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
