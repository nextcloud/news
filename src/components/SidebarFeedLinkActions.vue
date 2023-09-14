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
			{{ t("news", "Unpin from top") }}
		</NcActionButton>
		<NcActionButton v-if="!feed.pinned"
			icon="icon-pinned"
			@click="setPinned(true)">
			{{ t("news", "Pin to top") }}
		</NcActionButton>
		<NcActionButton v-if="feed.ordering === FEED_ORDER.NEWEST"
			icon="icon-caret-dark"
			@click="setOrdering(FEED_ORDER.OLDEST)">
			{{ t("news", "Newest first") }}
		</NcActionButton>
		<NcActionButton v-else-if="feed.ordering === FEED_ORDER.OLDEST"
			icon="icon-caret-dark"
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
			{{ t("news", "Enable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.fullTextEnabled"
			icon="icon-full-text-enabled"
			@click="setFullText(false)">
			{{ t("news", "Disable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.UNREAD"
			icon="icon-updatemode-default"
			@click="setUpdateMode(FEED_UPDATE_MODE.IGNORE)">
			{{ t("news", "Unread updated") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.IGNORE"
			icon="icon-updatemode-unread"
			@click="setUpdateMode(FEED_UPDATE_MODE.UNREAD)">
			{{ t("news", "Ignore updated") }}
		</NcActionButton>
		<NcActionButton icon="icon-rss"
			@click="alert('TODO: Open Feed URL')">
			{{ t("news", "Open feed URL") }}
		</NcActionButton>
		<NcActionButton icon="icon-icon-rename"
			@click="alert('TODO: Rename')">
			{{ t("news", "Rename") }}
		</NcActionButton>
		<NcActionButton icon="icon-delete"
			@click="alert('TODO: Delete Feed')">
			{{ t("news", "Delete") }}
		</NcActionButton>
	</span>
</template>

<script lang="ts">

import { mapState } from 'vuex'
import Vue from 'vue'

import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import { FEED_ORDER, FEED_UPDATE_MODE } from '../dataservices/feed.service'

import { ACTIONS } from '../store'
import { Feed } from '../types/Feed'

// import { Feed } from '../types/Feed'

// TODO?
const SidebarFeedLinkState = { }

export default Vue.extend({
	components: {
		NcActionButton,
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
		}
	},

	computed: {
		...mapState(SidebarFeedLinkState),
		feed(): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => {
				return feed.id === this.feedId
			})
		},
	},
	created() {
		// TODO: init?
	},
	methods: {
		alert(msg: string) {
			window.alert(msg)
		},
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
	},
})

</script>
