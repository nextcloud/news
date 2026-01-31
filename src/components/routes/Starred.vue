<template>
	<ContentTemplate
		v-if="!loading"
		:key="fetchKey"
		:listName="starredFeed ? starredFeed.title : t('news', 'Starred')"
		:listCount="starredFeed ? starredFeed.starredCount : items.starredCount"
		:items="starred"
		:fetchKey="fetchKey"
		@loadMore="fetchMore()" />
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { outOfScopeFilter, sortedFeedItems } from '../../utils/itemFilter.ts'

export default defineComponent({
	name: 'RoutesStarred',
	components: {
		ContentTemplate,
	},

	props: {
		/**
		 * Unique identifier of the feed whose starred entries this component displays.
		 *
		 * @type {string}
		 *
		 * Used to fetch the relevant entries from the backend and to filter the displayed items.
		 */
		starredFeedId: {
			type: String,
			required: false,
			default: undefined,
		},
	},

	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			const starred = this.$store.getters.starred

			let items = this.starredFeedId
				? starred.filter((item: FeedItem) => item.feedId === this.id)
				: starred
			/*
			 * Sorting items is needed because the allItems array can contain
			 * different orderings due to possible individual feed sorting
			 */
			items = sortedFeedItems(items, this.oldestFirst)
			return outOfScopeFilter(this.$store, items, 'starred')
		},

		loading() {
			return this.$store.getters.loading
		},

		oldestFirst() {
			return this.$store.getters.oldestFirst
		},

		id(): number {
			return this.starredFeedId ? Number(this.starredFeedId) : 0
		},

		fetchKey(): string {
			return this.starredFeedId ? 'starred-' + this.starredFeedId : 'starred'
		},

		starredFeed(): Feed {
			return this.starredFeedId
				? this.$store.getters.feeds.find((feed: Feed) => feed.id === this.id)
				: undefined
		},
	},

	created() {
		/*
		 * When sorting newest to oldest lastItemLoaded needs to be reset to get new items for this route
		 */
		if (this.oldestFirst === false) {
			this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: this.fetchKey, lastItem: undefined })
		}
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems[this.fetchKey]) {
				this.$store.dispatch(ACTIONS.FETCH_STARRED, { feedId: this.id })
			}
		},
	},
})
</script>
