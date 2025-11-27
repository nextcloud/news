<template>
	<ContentTemplate
		v-if="!loading"
		:list-name="t('news', 'Starred')"
		:list-count="items.starredCount"
		:items="starred"
		:fetch-key="fetchKey"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'Starred') }}
			<NcCounterBubble class="counter-bubble" :count="feedId ? starred.length : items.starredCount" />
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { outOfScopeFilter, sortedFeedItems } from '../../utils/itemFilter.ts'

export default defineComponent({
	name: 'RoutesStarred',
	components: {
		ContentTemplate,
		NcCounterBubble,
	},

	props: {
		/**
		 * Unique identifier of the feed whose starred entries this component displays.
		 *
		 * @type {number}
		 * @example 42 or 0
		 *
		 * Used to fetch the relevant entries from the backend and to filter the displayed items.
		 */
		feedId: {
			type: Number,
			required: false,
			default: 0,
		},
	},

	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			let items = this.$store.getters.starred
			if (this.feedId) {
				items = this.$store.getters.starred.filter((item: FeedItem) => item.feedId === this.id)
			}
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
			return Number(this.feedId)
		},

		fetchKey(): string {
			return (this.feedId === 0) ? 'starred' : 'starred-' + this.feedId
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
			if (!this.$store.state.items.fetchingItems[this.fetchKey]) {
				this.$store.dispatch(ACTIONS.FETCH_STARRED, { feedId: this.id })
			}
		},
	},
})
</script>
