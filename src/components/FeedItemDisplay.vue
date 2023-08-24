<template>
	<div class="feed-item-display">
		<div class="action-bar">
			<NcActions :force-menu="true">
				<template #icon>
					<ShareVariant />
				</template>
				<NcActionButton>
					<template #default>
						<!-- TODO: Share Menu --> TODO
					</template>
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
			</NcActions>
			<StarIcon :class="{'starred': item.starred }" @click="toggleStarred(item)" />
			<CloseIcon @click="clearSelected()" />
		</div>
		<div class="article">
			<div class="heading">
				<h1 :dir="item.rtl && 'rtl'">
					<a target="_blank"
						rel="noreferrer"
						:href="item.url"
						:title="item.title">
						{{ item.title }}
					</a>
				</h1>
				<time class="date" :title="formatDate(item.pubDate*1000, 'yyyy-MM-dd HH:mm:ss')" :datetime="formatDate(item.pubDate*1000, 'yyyy-MM-ddTHH:mm:ssZ')">
					{{ formatDate(item.pubDate*1000) }}
				</time>
			</div>

			<div class="subtitle" :dir="item.rtl && 'rtl'">
				<span v-show="item.author !== undefined && item.author !== null && item.author.trim() !== ''" class="author">
					{{ t('news', 'by') }} {{ item.author }}
				</span>
				<span v-if="!item.sharedBy" class="source">{{ t('news', 'from') }}
					<!-- TODO: Fix link to feed -->
					<a :href="`#/items/feeds/${item.feedId}/`">
						{{ getFeed(item.feedId).title }}
						<img v-if="getFeed(item.feedId).faviconLink"
							:src="getFeed(item.feedId).faviconLink"
							alt="favicon"
							style="width: 16px">
					</a>
				</span>
				<span v-if="item.sharedBy">
					<span v-if="item.author">-</span>
					{{ t('news', 'shared by') }}
					{{ item.sharedByDisplayName }}
				</span>
			</div>

			<!-- TODO: Test audio/video -->
			<div v-if="getMediaType(item.enclosureMime) == 'audio'" class="enclosure">
				<button @click="play(item)">
					{{ t('news', 'Play audio') }}
				</button>
				<a class="button"
					:href="item.enclosureLink"
					target="_blank"
					rel="noreferrer">
					{{ t('news', 'Download audio') }}
				</a>
			</div>
			<div v-if="getMediaType(item.enclosureMime) == 'video'" class="enclosure">
				<video controls
					preload="none"
					news-play-one
					:src="item.enclosureLink"
					:type="item.enclosureMime" />
				<a class="button"
					:href="item.enclosureLink"
					target="_blank"
					rel="noreferrer">
					{{ t('news', 'Download video') }}
				</a>
			</div>

			<div v-if="item.mediaThumbnail" class="enclosure thumbnail">
				<a :href="item.enclosureLink"><img :src="item.mediaThumbnail" alt=""></a>
			</div>

			<div v-if="item.mediaDescription" class="enclosure description" v-html="item.mediaDescription" />

			<div class="body" :dir="item.rtl && 'rtl'" v-html="item.body" />
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'

import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import { Feed } from '../types/Feed'
import { FeedItem } from '../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../store'

export default Vue.extend({
	name: 'FeedItemDisplay',
	components: {
		CloseIcon,
		StarIcon,
		ShareVariant,
		NcActions,
		NcActionButton,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	data: () => {
		return {
			keepUnread: false,
		}
	},
	computed: {
		...mapState(['feeds']),
	},
	methods: {
		clearSelected() {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		},
		formatDate(epoch: number) {
			return new Date(epoch).toLocaleString()
		},
		formatDatetime(epoch: number) {
			return new Date(epoch).toISOString()
		},
		getFeed(id: number): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === id) || {}
		},
		getMediaType(mime: any): 'audio' | 'video' | false {
			// TODO: figure out how to check media type
			return false
		},
		play(item: any) {
			// TODO: implement play audio/video
		},
		toggleStarred(item: FeedItem): void {
			this.$store.dispatch(item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item })
		},
	},
})

</script>

<style>
	.feed-item-display {
		max-height: 100%;
		overflow-y: hidden;
		display: flex;
		flex-direction: column;
	}

	.article {
		padding: 0 50px 50px 50px;
		overflow-y: scroll;
		height: 100%;
	}

	.article .body {
		color: var(--color-main-text);
    font-size: 15px;
	}

	.article a {
		text-decoration: underline;
	}

	.article .body a {
		color: #3a84e4
	}

	.article .subtitle {
		color: var(--color-text-lighter);
    font-size: 15px;
    padding: 25px 0;
	}

	.article .author {
		color: var(--color-text-lighter);
    font-size: 15px;
	}

	.article img {
		width: 100%;
	}

	.article h1 {
		font-weight: bold;
    font-size: 17px;
		margin-top: 25px;
	}

	.action-bar {
		padding: 20px;

		display: flex;
		justify-content: right
	}

	.action-bar .material-design-icon{
		cursor: pointer;
		margin: 5px;
	}
</style>
