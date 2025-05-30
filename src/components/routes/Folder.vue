<template>
	<ContentTemplate
		v-if="!loading"
		:key="'folder-' + folderId"
		:items="items"
		:fetch-key="'folder-' + folderId"
		@mark-read="markRead()"
		@load-more="fetchMore()">
		<template #header>
			{{ folder ? folder.name : '' }}
			<NcCounterBubble v-if="folder" class="counter-bubble" :count="unreadCount" />
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'
import type { Folder } from '../../types/Folder.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS } from '../../store/index.ts'
import { updateUnreadCache } from '../../utils/unreadCache.ts'

export default defineComponent({
	name: 'RoutesFolder',
	components: {
		ContentTemplate,
		NcCounterBubble,
	},

	props: {
		/**
		 * ID of the folder to display
		 */
		folderId: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			folderItems: [],
		}
	},

	computed: {
		...mapState(['items']),
		folder(): Folder {
			return this.$store.getters.folders.find((folder: Folder) => folder.id === this.id)
		},

		items(): FeedItem[] {
			return this.folderItems ?? []
		},

		id(): number {
			return Number(this.folderId)
		},

		loading() {
			return this.$store.getters.loading
		},

		showAll() {
			return this.$store.getters.showAll
		},

		unreadCount(): number {
			const totalUnread = this.$store.getters.feeds
				.filter((feed: Feed) => feed.folderId === this.id)
				.reduce((acc: number, feed: Feed) => {
					acc += feed.unreadCount
					return acc
				}, 0)

			return totalUnread
		},
	},

	created() {
		this.$watch(
			() => [this.folderId, this.showAll, this.$store.state.items.allItems],
			([newFolderId, newShowAll, newItems], [oldFolderId, oldShowAll] = []) => {
				const feeds: Array<number> = this.$store.getters.feeds.filter((feed: Feed) => feed.folderId === this.id).map((feed: Feed) => feed.id)
				/*
				 * Filter out read items if showAll is disabled
				 */
				const newFolderItems = newItems.filter((item: FeedItem) => {
					return feeds.includes(item.feedId) && (this.showAll || item.unread)
				}) || []
				/*
				 * If showAll is disabled an unread cache is needed so items read
				 * aren't removed from the list until changing the route or reload
				 */
				if (this.showAll) {
					this.folderItems = newFolderItems
				} else {
					/*
					 * clear unread item cache
					 */
					if (newFolderId !== oldFolderId || newShowAll !== oldShowAll) {
						this.folderItems = []
					}
					updateUnreadCache(newFolderItems, this.folderItems)
				}
			},
			{ immediate: true },
		)
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems['folder-' + this.folderId]) {
				this.$store.dispatch(ACTIONS.FETCH_FOLDER_FEED_ITEMS, { folderId: this.id })
			}
		},

		async markRead() {
			const feeds = this.$store.getters.feeds.filter((feed: Feed) => {
				return feed.folderId === this.folder.id
			})
			feeds.forEach((feed: Feed) => {
				this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed })
			})
		},
	},
})
</script>

<style scoped>
.counter-bubble {
	display: inline-block;
	vertical-align: sub;
	margin-inline-start: 10px;
}
</style>
