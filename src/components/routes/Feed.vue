<template>
	<ContentTemplate :items="items"
		:fetch-key="'feed-' + feedId"
		@load-more="fetchMore()">
		<template #header>
			{{ feed ? feed.title : '' }}
			<NcCounterBubble v-if="feed" class="counter-bubble">
				{{ feed.unreadCount }}
			</NcCounterBubble>
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import ContentTemplate from '../ContentTemplate.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'
import { Feed } from '../../types/Feed'

export default Vue.extend({
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
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === this.id)
		},
		items(): FeedItem[] {
			return this.$store.state.items.allItems.filter((item: FeedItem) => {
				return item.feedId === this.id
			}) || []
		},
		id(): number {
			return Number(this.feedId)
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		this.fetchMore()
		this.$watch(() => this.$route.params, this.fetchMore)
	},
	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems['feed-' + this.feedId]) {
			  this.$store.dispatch(ACTIONS.FETCH_FEED_ITEMS, { feedId: this.id })
			}
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
