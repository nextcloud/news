<template>
	<div class="route-container">
		<div class="header">
			{{ folder ? folder.name : '' }}
			<NcCounterBubble v-if="folder" class="counter-bubble">
				{{ unreadCount }}
			</NcCounterBubble>
		</div>

		<FeedItemDisplayList :items="items" :fetch-key="'folder-'+folderId" @load-more="fetchMore()" />
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'
import { Feed } from '../../types/Feed'
import { Folder } from '../../types/Folder'

export default Vue.extend({
	components: {
		NcCounterBubble,
		FeedItemDisplayList,
	},
	props: {
		folderId: {
			type: String,
			required: true,
		},
	},
	computed: {
		...mapState(['items', 'feeds', 'folders']),
		folder(): Folder {
			return this.$store.getters.folders.find((folder: Folder) => folder.id === this.id)
		},
		items(): FeedItem[] {
			const feeds: Array<number> = this.$store.getters.feeds.filter((feed: Feed) => feed.folderId === this.id).map((feed: Feed) => feed.id)

			return this.$store.state.items.allItems.filter((item: FeedItem) => {
				return feeds.includes(item.feedId)
			}) || []
		},
		id(): number {
			return Number(this.folderId)
		},
		unreadCount(): number {
			const totalUnread = this.$store.getters.feeds
				.filter((feed: Feed) => feed.folderId === this.id)
				.reduce((acc: number, feed: Feed) => { acc += feed.unreadCount; return acc }, 0)

			return totalUnread
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		this.fetchMore()
		this.$watch(() => this.$route.params, this.fetchMore)
	},
	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems['folder-' + this.folderId]) {
			  this.$store.dispatch(ACTIONS.FETCH_FOLDER_FEED_ITEMS, { folderId: this.id })
			}
		},
	},
})
</script>

<style scoped>
.route-container {
	height: 100%;
}

.header {
	padding-left: 50px;
	position: absolute;
	top: 1em;
	font-weight: 700;
}

.counter-bubble {
	display: inline-block;
	vertical-align: sub;
	margin-left: 10px;
}

</style>
