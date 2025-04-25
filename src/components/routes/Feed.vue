<template>
	<ContentTemplate v-if="!loading"
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
import { defineComponent } from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'

import ContentTemplate from '../ContentTemplate.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'
import { Feed } from '../../types/Feed'

export default defineComponent({
	components: {
		ContentTemplate,
		NcCounterBubble,
	},
	props: {
		feedId: {
			type: String,
			required: true,
		},
	},
	computed: {
		...mapState(['items', 'feeds']),
		feed(): Feed {
			const feeds = this.$store.getters.feeds
			return feeds.find((feed: Feed) => feed.id === this.id)
		},
		items(): FeedItem[] {
			return this.$store.state.items.allItems.filter((item: FeedItem) => {
				return item.feedId === this.id
			}) || []
		},
		id(): number {
			return Number(this.feedId)
		},
		loading() {
			return this.$store.getters.loading
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		this.fetchMore()
		this.$watch(() => this.$route.params, this.fetchMore)
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
	margin-left: 10px;
}
</style>
