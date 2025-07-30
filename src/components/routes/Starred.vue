<template>
	<ContentTemplate
		v-if="!loading"
		:items="starred"
		:list-name="t('news', 'Starred')"
		:list-count="items.starredCount"
		fetch-key="starred"
		@load-more="fetchMore()" />
</template>

<script lang="ts">
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

	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			let items = this.$store.getters.starred
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
	},

	created() {
		/*
		 * When sorting newest to oldest lastItemLoaded needs to be reset to get new items for this route
		 */
		if (this.oldestFirst === false) {
			this.$store.commit(MUTATIONS.SET_LAST_ITEM_LOADED, { key: 'starred', lastItem: undefined })
		}
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.starred) {
				this.$store.dispatch(ACTIONS.FETCH_STARRED)
			}
		},
	},
})
</script>
