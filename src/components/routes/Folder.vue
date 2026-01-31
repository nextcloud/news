<template>
	<ContentTemplate
		v-if="!loading"
		:key="'folder-' + folderId"
		:items="items"
		:listName="folder ? folder.name : ''"
		:listCount="folder ? unreadCount : 0"
		:fetchKey="'folder-' + folderId"
		@markRead="markRead()"
		@loadMore="fetchMore()" />
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'
import type { Folder } from '../../types/Folder.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { outOfScopeFilter, sortedFeedItems } from '../../utils/itemFilter.ts'
import { updateUnreadCache } from '../../utils/unreadCache.ts'

export default defineComponent({
	name: 'RoutesFolder',
	components: {
		ContentTemplate,
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
			let items = this.folderItems ?? []
			/*
			 * Sorting items is needed because the allItems array can contain
			 * different orderings due to possible individual feed sorting
			 */
			items = sortedFeedItems(items, this.oldestFirst)
			return outOfScopeFilter(this.$store, items, 'folder-' + this.folderId)
		},

		id(): number {
			return Number(this.folderId)
		},

		itemReset() {
			return this.$store.state.items.newestItemId === 0
		},

		loading() {
			return this.$store.getters.loading
		},

		oldestFirst() {
			return this.$store.getters.oldestFirst
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

	watch: {
		folderId: {
			handler() {
				/*
				 * When sorting newest to oldest lastItemLoaded needs to reset to get new items for this route
				 */
				if (this.oldestFirst === false) {
					this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: 'folder-' + this.folderId, lastItem: undefined })
				}
			},

			immediate: true,
		},
	},

	created() {
		this.$watch(
			() => [this.folderId, this.itemReset, this.$store.state.items.allItems],
			([newFolderId, newLastItem, newItems], [oldFolderId] = []) => {
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
					if (newFolderId !== oldFolderId || newLastItem) {
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
