<template>
	<NcAppContent>
		<template #list>
			<div class="header">
				{{ feed ? feed.title : '' }}
				<NcCounterBubble v-if="feed" class="counter-bubble">
					{{ feed.unreadCount }}
				</NcCounterBubble>
			</div>

			<FeedItemDisplayList :items="items" :fetch-key="'feed-'+feedId" @load-more="fetchMore()" />
		</template>
	</NcAppContent>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'
import { Feed } from '../../types/Feed'

export default Vue.extend({
	components: {
		NcAppContent,
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
			  this.$store.dispatch(ACTIONS.FETCH_FEED_ITEMS, { feedId: this.id })
			}
		},
	},
})
</script>

<style scoped>

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
