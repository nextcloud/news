<template>
	<span>
		<NcActionButton v-if="feed.unreadCount > 0"
			icon="icon-checkmark"
			@click="markRead">
			{{ t("news", "Mark read") }}
		</NcActionButton>
		<NcActionButton v-if="feed.pinned"
			icon="icon-pinned"
			@click="setPinned(false)">
			<template #icon>
				<PinOffIcon />
			</template>
			{{ t("news", "Unpin from top") }}
		</NcActionButton>
		<NcActionButton v-if="!feed.pinned"
			icon="icon-pinned"
			@click="setPinned(true)">
			<template #icon>
				<PinIcon />
			</template>
			{{ t("news", "Pin to top") }}
		</NcActionButton>
		<NcActionButton v-if="feed.ordering === FEED_ORDER.NEWEST"
			icon="icon-caret-dark"
			@click="setOrdering(FEED_ORDER.OLDEST)">
			{{ t("news", "Newest first") }}
		</NcActionButton>
		<NcActionButton v-else-if="feed.ordering === FEED_ORDER.OLDEST"
			icon="icon-caret-dark feed-reverse-ordering"
			@click="setOrdering(FEED_ORDER.DEFAULT)">
			{{ t("news", "Oldest first") }}
		</NcActionButton>
		<NcActionButton v-else
			icon="icon-caret-dark"
			@click="setOrdering(FEED_ORDER.NEWEST)">
			{{ t("news", "Default order") }}
		</NcActionButton>
		<NcActionButton v-if="!feed.fullTextEnabled"
			icon="icon-full-text-disabled"
			@click="setFullText(true)">
			<template #icon>
				<TextShortIcon />
			</template>
			{{ t("news", "Enable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.fullTextEnabled"
			icon="icon-full-text-enabled"
			@click="setFullText(false)">
			<template #icon>
				<TextLongIcon />
			</template>
			{{ t("news", "Disable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.UNREAD"
			icon="icon-updatemode-default"
			@click="setUpdateMode(FEED_UPDATE_MODE.IGNORE)">
			<template #icon>
				<span class="custom-icon">
					<img :src="UnreadSvg">
				</span>
			</template>
			{{ t("news", "Unread updated") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.IGNORE"
			icon="icon-updatemode-unread"
			@click="setUpdateMode(FEED_UPDATE_MODE.UNREAD)">
			<template #icon>
				<span class="custom-icon">
					<img :src="IgnoreSvg">
				</span>
			</template>
			{{ t("news", "Ignore updated") }}
		</NcActionButton>
		<NcActionButton icon="icon-rename"
			@click="rename()">
			{{ t("news", "Rename") }}
		</NcActionButton>
		<NcActionButton icon="icon-arrow"
			@click="move()">
			<template #icon>
				<ArrowRightIcon />
			</template>
			{{ t("news", "Move") }}
		</NcActionButton>
		<NcActionButton icon="icon-delete"
			@click="deleteFeed()">
			{{ t("news", "Delete") }}
		</NcActionButton>
		<NcAppNavigationItem :title="t('news', 'Open Feed URL')"
			:href="feed.location">
			<template #icon>
				<RssIcon />
			</template>
		</NcAppNavigationItem>
		<MoveFeed v-if="showMoveFeed" :feed="feed" @close="closeShowMoveFeed()" />
	</span>
</template>

<script lang="ts">

import Vue from 'vue'

import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'

import { FEED_ORDER, FEED_UPDATE_MODE } from '../dataservices/feed.service'

import RssIcon from 'vue-material-design-icons/Rss.vue'
import PinIcon from 'vue-material-design-icons/Pin.vue'
import PinOffIcon from 'vue-material-design-icons/PinOff.vue'
import TextShortIcon from 'vue-material-design-icons/TextShort.vue'
import TextLongIcon from 'vue-material-design-icons/TextLong.vue'
import ArrowRightIcon from 'vue-material-design-icons/ArrowRight.vue'

import { ACTIONS } from '../store'
import { Feed } from '../types/Feed'
import MoveFeed from './MoveFeed.vue'
const UnreadSvg = require('../../img/updatemodeunread.svg')
const IgnoreSvg = require('../../img/updatemodedefault.svg')

export default Vue.extend({
	components: {
		MoveFeed,
		NcActionButton,
		NcAppNavigationItem,
		RssIcon,
		PinIcon,
		PinOffIcon,
		TextShortIcon,
		TextLongIcon,
		ArrowRightIcon,
	},
	props: {
		feedId: {
			type: Number,
			required: true,
		},
	},
	data: () => {
		return {
			FEED_ORDER,
			FEED_UPDATE_MODE,
			UnreadSvg,
			IgnoreSvg,
			showMoveFeed: false,
		}
	},

	computed: {
		feed(): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => {
				return feed.id === this.feedId
			})
		},
	},
	methods: {
		markRead() {
			this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed: this.feed })
		},
		setPinned(pinned: boolean) {
			this.$store.dispatch(ACTIONS.FEED_SET_PINNED, { feed: this.feed, pinned })
		},
		setOrdering(ordering: FEED_ORDER) {
			this.$store.dispatch(ACTIONS.FEED_SET_ORDERING, { feed: this.feed, ordering })
		},
		setFullText(fullTextEnabled: boolean) {
			this.$store.dispatch(ACTIONS.FEED_SET_FULL_TEXT, { feed: this.feed, fullTextEnabled })
		},
		setUpdateMode(updateMode: FEED_UPDATE_MODE) {
			this.$store.dispatch(ACTIONS.FEED_SET_UPDATE_MODE, { feed: this.feed, updateMode })
		},
		rename() {
			const title = window.prompt(t('news', 'Rename Feed'), this.feed.title)

			// null when user presses escape (do nothing)
			if (title !== null) {
				this.$store.dispatch(ACTIONS.FEED_SET_TITLE, { feed: this.feed, title })
			}
		},
		move() {
			this.showMoveFeed = true
		},
		closeShowMoveFeed() {
			this.showMoveFeed = false
		},
		deleteFeed() {
			const shouldDelete = window.confirm(t('news', 'Are you sure you want to delete?'))

			if (shouldDelete) {
				this.$store.dispatch(ACTIONS.FEED_DELETE, { feed: this.feed })
			}
		},
	},
})

</script>

<style>
.custom-icon {
	width: 44px;
	height: 44px;
	display: flex;
	align-self: center;
	justify-self: center;
	align-items: center;
	justify-content: center;
}

.feed-reverse-ordering {
	transform: rotate(180deg);
}
</style>
