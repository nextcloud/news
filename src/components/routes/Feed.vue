<template>
	<ContentTemplate
		v-if="!loading"
		:key="'feed-' + feedId"
		:items="items"
		:fetch-key="'feed-' + feedId"
		@mark-read="markRead()"
		@load-more="fetchMore()">
		<template #header>
			{{ feed ? feed.title : '' }}
			<NcCounterBubble v-if="feed" class="counter-bubble" :count="feed.unreadCount" />
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS } from '../../store/index.ts'
import { updateUnreadCache } from '../../utils/unreadCache.ts'

export default defineComponent({
	name: 'RoutesFeed',
	components: {
		ContentTemplate,
		NcCounterBubble,
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
			return this.feedItems ?? []
		},

		id(): number {
			return Number(this.feedId)
		},

		loading() {
			return this.$store.getters.loading
		},

		showAll() {
			return this.$store.getters.showAll
		},
	},

	created() {
		this.$watch(
			() => [this.feedId, this.showAll, this.$store.state.items.allItems],
			([newFeedId, newShowAll, newItems], [oldFeedId, oldShowAll] = []) => {
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
					if (newFeedId !== oldFeedId || newShowAll !== oldShowAll) {
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

<style scoped>
.counter-bubble {
	display: inline-block;
	vertical-align: sub;
	margin-inline-start: 10px;
}
</style>
