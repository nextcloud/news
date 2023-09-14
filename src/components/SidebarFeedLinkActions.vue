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
			@click="alert('TODO: Newest First')">
			{{ t("news", "Newest first") }}
		</NcActionButton>
		<NcActionButton v-if="feed.ordering === FEED_ORDER.OLDEST"
			icon="icon-caret-dark"
			@click="alert('TODO: Oldest first')">
			{{ t("news", "Oldest first") }}
		</NcActionButton>
		<NcActionButton v-if="feed.ordering === FEED_ORDER.DEFAULT"
			icon="icon-caret-dark"
			@click="alert('TODO: Default Order')">
			{{ t("news", "Default order") }}
		</NcActionButton>
		<NcActionButton v-if="!feed.enableFullText"
			icon="icon-full-text-disabled"
			@click="alert('TODO: Enable Full Text')">
			{{ t("news", "Enable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.enableFullText"
			icon="icon-full-text-enabled"
			@click="alert('TODO: DIsable Full Text')">
			{{ t("news", "Disable full text") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.UNRAD"
			icon="icon-updatemode-default"
			@click="alert('TODO: Unread Updated')">
			{{ t("news", "Unread updated") }}
		</NcActionButton>
		<NcActionButton v-if="feed.updateMode === FEED_UPDATE_MODE.IGNORE"
			icon="icon-updatemode-unread"
			@click="alert('TOODO: Ignore UPdated')">
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
	},
})

</script>
