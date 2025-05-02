<template>
	<div
		class="feed-item-display"
		:class="{ screenreader: screenReaderMode }"
		:aria-posinset="itemIndex"
		:aria-setsize="itemCount"
		@focusin="selectItemOnFocus">
		<ShareItem v-if="showShareMenu" :item-id="item.id" @close="closeShareMenu()" />

		<div class="action-bar">
			<NcActions
				v-show="!splitModeOff"
				class="action-bar-nav"
				:inline="4">
				<NcActionButton
					:title="t('news', 'Previous Item')"
					@click="prevItem">
					{{ t('news', 'Previous') }}
					<template #icon>
						<ArrowLeftThickIcon />
					</template>
				</NcActionButton>
				<NcActionButton
					:title="t('news', 'Next Item')"
					@click="nextItem">
					{{ t('news', 'Next') }}
					<template #icon>
						<ArrowRightThickIcon />
					</template>
				</NcActionButton>
			</NcActions>
			<NcActions :inline="4">
				<NcActionButton
					:title="t('news', 'Share within Instance')"
					@click="showShareMenu = true">
					{{ t('news', 'Share within Instance') }}
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
				<NcActionButton
					:title="t('news', 'Toggle star article')"
					@click="toggleStarred">
					{{ t('news', 'Toggle star article') }}
					<template #icon>
						<StarIcon :class="{ starred: item.starred }" :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="item.unread"
					:title="t('news', 'Mark read')"
					@click="toggleRead">
					{{ t('news', 'Mark read') }}
					<template #icon>
						<EyeIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="!item.unread"
					:title="t('news', 'Mark unread')"
					@click="toggleRead">
					{{ t('news', 'Mark unread') }}
					<template #icon>
						<EyeCheckIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="!screenReaderMode"
					:title="t('news', 'Close details')"
					@click="splitModeOff ? closeDetails() : clearSelected()">
					{{ t('news', 'Close details') }}
					<template #icon>
						<CloseIcon :size="24" />
					</template>
				</NcActionButton>
			</NcActions>
		</div>
		<div class="article">
			<div class="heading">
				<h1 :dir="item.rtl && 'rtl'">
					<a
						target="_blank"
						rel="noreferrer"
						:href="item.url"
						:title="item.title">
						{{ item.title }}
					</a>
				</h1>
				<time class="date" :title="formatDate(item.pubDate)" :datetime="formatDateISO(item.pubDate)">
					{{ formatDate(item.pubDate) }}
				</time>
			</div>

			<div class="subtitle" :dir="item.rtl && 'rtl'">
				<span v-show="item.author !== undefined && item.author !== null && item.author.trim() !== ''" class="author">
					{{ t('news', 'by') }} {{ item.author }}
				</span>
				<span v-if="!item.sharedBy" class="source">{{ t('news', 'from') }}
					<a :href="`#/feed/${item.feedId}/`">
						{{ getFeed(item.feedId).title }}
						<img
							v-if="getFeed(item.feedId).faviconLink"
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
				<a
					class="button"
					style="text-decoration: none;"
					:href="item.enclosureLink"
					target="_blank"
					rel="noreferrer">
					{{ t('news', 'Download audio') }}
				</a>
			</div>
			<div v-if="getMediaType(item.enclosureMime) == 'video'" class="enclosure video">
				<video
					controls
					preload="none"
					:src="item.enclosureLink"
					:type="item.enclosureMime"
					:style="{ 'background-image': 'url(' + item.mediaThumbnail + ')' }"
					@play="stopAudio()" />
				<div class="download">
					<a
						class="button"
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
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { defineComponent } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import ArrowLeftThickIcon from 'vue-material-design-icons/ArrowLeftThick.vue'
import ArrowRightThickIcon from 'vue-material-design-icons/ArrowRightThick.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import ShareItem from '../ShareItem.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { formatDate, formatDateISO } from '../../utils/dateUtils.ts'

export default defineComponent({
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
		ArrowLeftThickIcon,
		ArrowRightThickIcon,
	},

	props: {
		/**
		 * The item to display
		 */
		item: {
			type: Object,
			required: true,
		},

		/**
		 * The number of items in the current list
		 */
		itemCount: {
			type: Number,
			required: false,
			default: null,
		},

		/**
		 * The index of the item in the current list
		 */
		itemIndex: {
			type: Number,
			required: false,
			default: null,
		},
	},

	emits: {
		'click-item': () => true,
		'show-details': () => true,
		'prev-item': () => true,
		'next-item': () => true,
	},

	data: () => {
		return {
			keepUnread: false,
			showShareMenu: false,
		}
	},

	computed: {
		screenReaderMode() {
			return this.$store.getters.displaymode === '2'
		},

		splitModeOff() {
			return this.$store.getters.splitmode === '2'
		},
	},

	created() {
		// create shortcuts
		if (this.splitModeOff && !this.screenReaderMode) {
			useHotKey('Escape', this.closeDetails)
		}
	},

	methods: {
		formatDate,
		formatDateISO,
		/**
		 * Sends message to state to clear the selectedId number
		 */
		clearSelected(): void {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		},

		/**
		 * Use parent click handler to select item when focused,
		 * needed by screen reader navigation
		 */
		selectItemOnFocus(): void {
			if (this.screenReaderMode && this.$store.getters.selected !== this.item) {
				this.$emit('click-item')
			}
		},

		getFeed(id: number): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === id) || {}
		},

		/**
		 * Sends message to change the items starred property to the opposite value
		 */
		toggleStarred(): void {
			this.$store.dispatch(this.item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item: this.item })
		},

		toggleRead(): void {
			if (!this.item.keepUnread && this.item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item: this.item })
			} else {
				this.$store.dispatch(ACTIONS.MARK_UNREAD, { item: this.item })
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

		closeDetails() {
			this.$emit('show-details')
		},

		prevItem() {
			this.$emit('prev-item')
		},

		nextItem() {
			this.$emit('next-item')
		},
	},
})

</script>

<style lang="scss">
	$breakpoint-mobile: 1024px;

	.feed-item-display {
		overflow-y: hidden;
		display: flex;
		flex-direction: column;
	}

	.feed-item-display.screenreader {
		height: 111px;
	}

	.article {
		padding: 0 50px 50px 50px;
		width: 100%;
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
		max-width: 100%;
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
		padding: 10px 20px 0px 20px;

		display: flex;
		justify-content: right;
	}

	.action-bar-nav {
		flex-grow: 1;

		@media only screen and (width > $breakpoint-mobile) {
			display: none !important;
		}
	}

	.feed-item-display .action-bar .button-vue,
	.feed-item-display .action-bar .button-vue .button-vue__wrapper
	{
		width: 30px !important;
		min-width: 30px;
		min-height: 30px;
		height: 30px;
	}

</style>
