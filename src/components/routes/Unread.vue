<template>
	<ContentTemplate
		v-if="!loading"
		:items="unread"
		:listName="t('news', 'Unread Articles')"
		:listCount="items.unreadCount"
		fetchKey="unread"
		@loadMore="fetchMore()" />
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { outOfScopeFilter, sortedFeedItems } from '../../utils/itemFilter.ts'
import { updateUnreadCache } from '../../utils/unreadCache.ts'

export default defineComponent({
	name: 'RoutesUnread',
	components: {
		ContentTemplate,
	},

	data() {
		return {
			unreadCache: [],
		}
	},

	computed: {
		...mapState(['items']),
		newestItemId() {
			return this.$store.state.items.newestItemId === 0
		},

		lastItemLoaded() {
			return this.$store.state.items.lastItemLoaded.unread
		},

		unread(): FeedItem[] {
			let items = this.unreadCache ?? []
			/*
			 * Sorting items is needed because the allItems array can contain
			 * different orderings due to possible individual feed sorting
			 */
			items = sortedFeedItems(items, this.oldestFirst)
			return outOfScopeFilter(this.$store, items, 'unread')
		},

		loading() {
			return this.$store.getters.loading
		},

		oldestFirst() {
			return this.$store.getters.oldestFirst
		},
	},

	watch: {
		newestItemId(clearCache) {
			if (clearCache) {
				this.unreadCache = []
			}
		},

		// need cache so we aren't always removing items when they get read
		'$store.getters.unread': {
			handler(newItems) {
				updateUnreadCache(newItems, this.unreadCache)
			},

			immediate: true,
		},
	},

	created() {
		/*
		 * Reset the offset so that updated items can be fetched again when changing the route
		 */
		this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: 'unread', lastItem: undefined })
		this.$store.commit(MUTATIONS.SET_ALL_LOADED, { key: 'unread', loaded: false })
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.unread) {
				this.$store.dispatch(ACTIONS.FETCH_UNREAD)
			}
		},
	},
})
</script>
