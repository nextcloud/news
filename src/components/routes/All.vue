<template>
	<div class="route-container">
		<div class="header">
			{{ t('news', 'All Articles') }}
		</div>

		<FeedItemDisplayList :items="allItems"
			:fetch-key="'all'"
			@load-more="fetchMore()" />
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
	components: {
		FeedItemDisplayList,
	},
	computed: {
		...mapState(['items']),

		allItems(): FeedItem[] {
			return this.$store.getters.allItems
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
