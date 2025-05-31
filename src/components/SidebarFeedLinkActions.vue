<template>
	<span>
		<NcActionButton
			v-if="feed.unreadCount > 0"
			icon="icon-checkmark"
			:close-after-click="true"
			@click="markRead">
			{{ t("news", "Mark read") }}
		</NcActionButton>
		<NcActionButton
			v-if="feed.pinned"
			icon="icon-pinned"
			:close-after-click="true"
			@click="setPinned(false)">
			<template #icon>
				<PinOffIcon />
			</template>
			{{ t("news", "Unpin from top") }}
		</NcActionButton>
		<NcActionButton
			v-if="!feed.pinned"
			icon="icon-pinned"
			:close-after-click="true"
			@click="setPinned(true)">
			<template #icon>
				<PinIcon />
			</template>
			{{ t("news", "Pin to top") }}
		</NcActionButton>
		<NcActionButton
			v-if="feed.ordering === FEED_ORDER.NEWEST"
			icon="icon-caret-dark"
			@click="setOrdering(FEED_ORDER.OLDEST)">
			{{ t("news", "Newest first") }}
		</NcActionButton>
		<NcActionButton
			v-else-if="feed.ordering === FEED_ORDER.OLDEST"
			icon="icon-caret-dark feed-reverse-ordering"
			@click="setOrdering(FEED_ORDER.DEFAULT)">
			{{ t("news", "Oldest first") }}
		</NcActionButton>
		<NcActionButton
			v-else
			icon="icon-caret-dark"
			@click="setOrdering(FEED_ORDER.NEWEST)">
			{{ t("news", "Default order") }}
		</NcActionButton>
		<NcActionButton
			v-if="!feed.fullTextEnabled"
			icon="icon-full-text-disabled"
			@click="setFullText(true)">
			<template #icon>
				<TextShortIcon />
			</template>
			{{ t("news", "Enable full text") }}
		</NcActionButton>
		<NcActionButton
			v-if="feed.fullTextEnabled"
			icon="icon-full-text-enabled"
			@click="setFullText(false)">
			<template #icon>
				<TextLongIcon />
			</template>
			{{ t("news", "Disable full text") }}
		</NcActionButton>
		<NcActionButton
			v-if="feed.updateMode === FEED_UPDATE_MODE.UNREAD"
			icon="file-document-refresh"
			@click="setUpdateMode(FEED_UPDATE_MODE.IGNORE)">
			<template #icon>
				<FileDocumentRefresh />
			</template>
			{{ t("news", "Mark as unread on update") }}
		</NcActionButton>
		<NcActionButton
			v-if="feed.updateMode === FEED_UPDATE_MODE.IGNORE"
			icon="file-document-check"
			@click="setUpdateMode(FEED_UPDATE_MODE.UNREAD)">
			<template #icon>
				<FileDocumentCheck />
			</template>
			{{ t("news", "Keep read status on update") }}
		</NcActionButton>
		<NcActionButton
			icon="icon-rename"
			:close-after-click="true"
			@click="rename()">
			{{ t("news", "Rename") }}
		</NcActionButton>
		<NcActionButton
			icon="icon-arrow"
			:close-after-click="true"
			@click="$emit('move-feed')">
			<template #icon>
				<ArrowRightIcon />
			</template>
			{{ t("news", "Move") }}
		</NcActionButton>
		<NcActionButton
			icon="icon-delete"
			:close-after-click="true"
			@click="deleteFeed()">
			{{ t("news", "Delete") }}
		</NcActionButton>
		<NcAppNavigationItem
			:name="t('news', 'Open Feed URL')"
			:href="feed.location">
			<template #icon>
				<RssIcon />
			</template>
		</NcAppNavigationItem>
	</span>
</template>

<script lang="ts">

import type { Feed } from '../types/Feed.ts'

import { defineComponent } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcAppNavigationItem from '@nextcloud/vue/components/NcAppNavigationItem'
import ArrowRightIcon from 'vue-material-design-icons/ArrowRight.vue'
import FileDocumentCheck from 'vue-material-design-icons/FileDocumentCheck.vue'
import FileDocumentRefresh from 'vue-material-design-icons/FileDocumentRefresh.vue'
import PinIcon from 'vue-material-design-icons/Pin.vue'
import PinOffIcon from 'vue-material-design-icons/PinOff.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import TextLongIcon from 'vue-material-design-icons/TextLong.vue'
import TextShortIcon from 'vue-material-design-icons/TextShort.vue'
import { FEED_ORDER, FEED_UPDATE_MODE } from '../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../store/index.ts'

export default defineComponent({
	/*
	 * set custom component name because NcAppNavigationItem actions slot
	 * filters for NcAction* components
	 */

	name: 'NcActionButtonCustom',

	components: {
		NcActionButton,
		NcAppNavigationItem,
		RssIcon,
		PinIcon,
		PinOffIcon,
		TextShortIcon,
		TextLongIcon,
		ArrowRightIcon,
		FileDocumentRefresh,
		FileDocumentCheck,
	},

	props: {
		/**
		 * The feedId of the feed whose action menu is to be displayed
		 */
		feedId: {
			type: Number,
			required: true,
		},
	},

	emits: {
		'move-feed': () => true,
	},

	data: () => {
		return {
			FEED_ORDER,
			FEED_UPDATE_MODE,
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
			this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: 'feed-' + String(this.feedId), lastItem: undefined })
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
.feed-reverse-ordering {
	transform: rotate(180deg);
}
</style>
