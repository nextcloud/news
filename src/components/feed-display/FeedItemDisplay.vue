<template>
	<div class="feed-item-display">
		<ShareItem v-if="showShareMenu" :item-id="item.id" @close="closeShareMenu()" />

		<div class="action-bar">
			<NcActions>
				<NcActionButton :title="t('news', 'Share within Instance')" @click="showShareMenu = true">
					{{ t('news', 'Share') }}
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
			</NcActions>
			<StarIcon :class="{'starred': item.starred }" @click="toggleStarred(item)" />
			<EyeIcon v-if="item.unread" @click="toggleRead(item)" />
			<EyeCheckIcon v-if="!item.unread" @click="toggleRead(item)" />
			<CloseIcon @click="clearSelected()" />
			<button v-shortkey="{s: ['s'], l: ['l'], i: ['i']}" class="hidden" @shortkey="toggleStarred(item)" />
			<button v-shortkey="['o']" class="hidden" @shortkey="openUrl(item)" />
			<button v-shortkey="['u']" class="hidden" @shortkey="toggleRead(item)" />
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
					<a :href="`#/feed/${item.feedId}/`">
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

			<div v-if="getMediaType(item.enclosureMime) == 'audio'" class="enclosure audio">
				<button @click="playAudio(item)">
					{{ t('news', 'Play audio') }}
				</button>
				<a class="button"
					style="text-decoration: none;"
					:href="item.enclosureLink"
					target="_blank"
					rel="noreferrer">
					{{ t('news', 'Download audio') }}
				</a>
			</div>
			<div v-if="getMediaType(item.enclosureMime) == 'video'" class="enclosure video">
				<video controls
					preload="none"
					:src="item.enclosureLink"
					:type="item.enclosureMime"
					:style="{ 'background-image': 'url('+item.mediaThumbnail+')' }"
					@play="stopAudio()" />
				<div class="download">
					<a class="button"
						style="text-decoration: none;"
						:href="item.enclosureLink"
						target="_blank"
						rel="noreferrer">
						{{ t('news', 'Download video') }}
					</a>
				</div>
			</div>

			<div v-if="item.mediaThumbnail && getMediaType(item.enclosureMime) !== 'video'" class="enclosure thumbnail">
				<a v-if="item.enclosureLink" :href="item.enclosureLink"><img :src="item.mediaThumbnail" alt=""></a>
				<img v-else :src="item.mediaThumbnail" alt="">
			</div>

			<!-- eslint-disable vue/no-v-html -->
			<div v-if="item.mediaDescription" class="enclosure description" v-html="item.mediaDescription" />

			<div class="body" :dir="item.rtl && 'rtl'" v-html="item.body" />
			<!--eslint-enable-->
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

import ShareItem from '../ShareItem.vue'

import { Feed } from '../../types/Feed'
import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'

export default Vue.extend({
	name: 'FeedItemDisplay',
	components: {
		EyeCheckIcon,
		EyeIcon,
		CloseIcon,
		StarIcon,
		ShareVariant,
		NcActions,
		NcActionButton,
		ShareItem,
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
			showShareMenu: false,
		}
	},
	computed: {
		...mapState(['feeds']),
	},
	methods: {
		/**
		 * Sends message to state to clear the selectedId number
		 */
		clearSelected(): void {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		},
		/**
		 * Returns locale formatted date string
		 *
		 * @param {number} epoch date value in epoch format
		 * @return {string} locale formatted date string (based on users browser)
		 */
		formatDate(epoch: number): string {
			return new Date(epoch).toLocaleString()
		},

		/**
		 * Retrieve the feed by id number
		 *
		 * @param {number} id id of feed to fetch
		 * @return {Feed} associated Feed
		 */
		getFeed(id: number): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === id) || {}
		},
		/**
		 * Sends message to change the items starred property to the opposite value
		 *
		 * @param {FeedItem} item item to toggle starred status on
		 */
		toggleStarred(item: FeedItem): void {
			this.$store.dispatch(item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item })
		},

		toggleRead(item: FeedItem): void {
			if (item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item })
			} else {
				this.$store.dispatch(ACTIONS.MARK_UNREAD, { item })
			}
		},

		openUrl(item: FeedItem): void {
			// Open the item url in a new tab
			if (item.url) {
				window.open(item.url, '_blank')
			}
		},

		closeShareMenu() {
			this.showShareMenu = false
		},

		getMediaType(mime: string): 'audio' | 'video' | false {
			if (mime && mime.indexOf('audio') === 0) {
				return 'audio'
			} else if (mime && mime.indexOf('video') === 0) {
				return 'video'
			}
			return false
		},
		playAudio(item: FeedItem) {
			this.$store.commit(MUTATIONS.SET_PLAYING_ITEM, item)
		},
		stopAudio() {
			const audioElements = document.getElementsByTagName('audio')

			for (let i = 0; i < audioElements.length; i++) {
				audioElements[i].pause()
			}
		},
	},
})

</script>

<style>
	.feed-item-display {
		overflow-y: hidden;
		display: flex;
		flex-direction: column;
	}

	.article {
		padding: 0 50px 50px 50px;
		height: 100%;
		max-width: 1024px;
		margin-left: auto;
		margin-right: auto;
	}

	.article video {
		width: 100%;
		background-size: cover;
	}

	.article .enclosure.video {
		display: flex;
		flex-direction: column;
	}

	.article .enclosure.video .download {
		justify-content: center;
		display: flex;
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

	.article .body ul {
		margin: 7px 0;
		padding-left: 14px;
		list-style-type: disc;
	}

	.article .body ul li {
		cursor: default;
		line-height: 21px;
	}

	.article .body p {
		line-height: 1.5;
		margin: 7px 0 14px 0;
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
		height: auto;
	}

	.article h1 {
		font-weight: bold;
    font-size: 17px;
	}

	.article table {
		white-space: unset;
	}

	.action-bar {
		padding: 0px 20px 0px 20px;

		display: flex;
		justify-content: right
	}

	.action-bar .material-design-icon{
		cursor: pointer;
		margin: 5px;
	}

	.action-bar .material-design-icon:hover {
		color: var(--color-text-light);
	}
</style>
