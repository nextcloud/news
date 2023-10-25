<template>
	<NcAppContent>
		<template #list>
			<div class="header">
				{{ t('news', 'Starred') }}
				<NcCounterBubble class="counter-bubble">
					{{ items.starredCount }}
				</NcCounterBubble>
			</div>

			<FeedItemDisplayList :items="starred"
				:fetch-key="'starred'"
				:config="{ starFilter: false }"
				@load-more="fetchMore()" />
		</template>

		<div>
			<FeedItemDisplay v-if="selectedFeedItem" :item="selectedFeedItem" />
		</div>
	</NcAppContent>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'
import FeedItemDisplay from '../feed-display/FeedItemDisplay.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
	components: {
		NcAppContent,
		NcCounterBubble,
		FeedItemDisplayList,
		FeedItemDisplay
	},
	computed: {
		...mapState(['items']),

		starred(): FeedItem[] {
			return this.$store.getters.starred
		},
		selectedFeedItem(): FeedItem | undefined {
			return this.$store.getters.selected
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
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
