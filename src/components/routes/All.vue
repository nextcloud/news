<template>
	<NcAppContent>
		<template #list>
			<div class="header">
				{{ t('news', 'All Articles') }}
			</div>

			<FeedItemDisplayList :items="allItems"
				:fetch-key="'all'"
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

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'
import FeedItemDisplay from '../feed-display/FeedItemDisplay.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
	components: {
		NcAppContent,
		FeedItemDisplayList,
		FeedItemDisplay
	},
	computed: {
		...mapState(['items']),

		allItems(): FeedItem[] {
			return this.$store.getters.allItems
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
			if (!this.$store.state.items.fetchingItems.all) {
			  this.$store.dispatch(ACTIONS.FETCH_ITEMS)
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
