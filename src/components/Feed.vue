<template>
	<div class="route-container">
		<div class="header">
			{{ feed ? feed.title : '' }}
			<NcCounterBubble v-if="feed" class="counter-bubble">
				{{ feed.unreadCount }}
			</NcCounterBubble>
		</div>

		<FeedItemDisplayList :items="items" :fetch-key="'feed-'+feedId" @load-more="fetchMore()" />
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from './FeedItemDisplayList.vue'

import { FeedItem } from '../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../store'
import { Feed } from '../types/Feed'

export default Vue.extend({
	components: {
		NcCounterBubble,
		FeedItemDisplayList,
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
			  this.$store.dispatch(ACTIONS.FETCH_FEED_ITEMS, { feedId: this.id, start: this.items && this.items.length > 0 ? this.items[this.items.length - 1].id : 0 })
			}
		},
	},
})
</script>

<style scoped>
.route-container {
	height: 100%;
}

.header {
	padding-left: 50px;
	position: absolute;
	top: 1em;
	font-weight: 700;
}

.counter-bubble {
	display: inline-block;
	vertical-align: sub;
	margin-left: 10px;
}

</style>
