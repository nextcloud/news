<template>
	<ContentTemplate
		v-if="!loading"
		:key="'feed-' + feedId"
		:items="items"
		:list-name="feed ? feed.title : ''"
		:list-count="feed ? feed.unreadCount : 0"
		:fetch-key="'feed-' + feedId"
		@mark-read="markRead()"
		@load-more="fetchMore()" />
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { getOldestFirst, outOfScopeFilter, sortedFeedItems } from '../../utils/itemFilter.ts'
import { updateUnreadCache } from '../../utils/unreadCache.ts'

export default defineComponent({
	name: 'RoutesFeed',
	components: {
		ContentTemplate,
	},

	props: {
		/**
		 * ID of the feed to display
		 */
		feedId: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			feedItems: [],
		}
	},

	computed: {
		...mapState(['items']),
		feed(): Feed {
			const feeds = this.$store.getters.feeds
			return feeds.find((feed: Feed) => feed.id === this.id)
		},

		items(): FeedItem[] {
			let items = this.feedItems ?? []
			/*
			 * Sorting items is needed because the allItems array can contain
			 * different orderings due to possible individual feed sorting
			 */
			items = sortedFeedItems(items, this.oldestFirst)
			return outOfScopeFilter(this.$store, items, 'feed-' + this.feedId)
		},

		id(): number {
			return Number(this.feedId)
		},

		oldestFirst() {
			return getOldestFirst(this.$store, 'feed-' + this.feedId)
		},

		itemReset() {
			return this.$store.state.items.newestItemId === 0
		},

		loading() {
			return this.$store.getters.loading
		},

		showAll() {
			return this.$store.getters.showAll
		},
	},

	watch: {
		feedId: {
			handler() {
				/*
				 * When sorting newest to oldest lastItemLoaded needs to be reset to get new items for this route
				 */
				if (this.oldestFirst === false) {
					this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: 'feed-' + this.feedId, lastItem: undefined })
				}
			},

			immediate: true,
		},
	},

	created() {
		this.$watch(
			() => [this.feedId, this.itemReset, this.$store.state.items.allItems],
			([newFeedId, newLastItem, newItems], [oldFeedId] = []) => {
				/*
				 * Filter out read items if showAll is disabled
				 */
				const newFeedItems = newItems.filter((item: FeedItem) => {
					return item.feedId === this.id && (this.showAll || item.unread)
				}) || []
				/*
				 * If showAll is disabled an unread cache is needed so items read
				 * aren't removed from the list until changing the route or reload
				 */
				if (this.showAll) {
					this.feedItems = newFeedItems
				} else {
					/*
					 * clear unread item cache
					 */
					if (newFeedId !== oldFeedId || newLastItem) {
						this.feedItems = []
					}
					updateUnreadCache(newFeedItems, this.feedItems)
				}
			},
			{ immediate: true },
		)
	},

	methods: {
		async fetchMore() {
			if (!this.loading && !this.$store.state.items.fetchingItems['feed-' + this.feedId]) {
				this.$store.dispatch(ACTIONS.FETCH_FEED_ITEMS, { feedId: this.id })
			}
		},

		async markRead() {
			this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed: this.feed })
		},
	},
})
</script>
