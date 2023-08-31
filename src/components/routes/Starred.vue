<template>
	<div class="route-container">
		<div class="header">
			Starred
			<NcCounterBubble class="counter-bubble">
				{{ items.starredCount }}
			</NcCounterBubble>
		</div>

		<FeedItemDisplayList :items="starred"
			:fetch-key="'starred'"
			:config="{ starFilter: false }"
			@load-more="fetchMore()" />
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
	components: {
		NcCounterBubble,
		FeedItemDisplayList,
	},
	computed: {
		...mapState(['items']),

		starred(): FeedItem[] {
			return this.$store.getters.starred
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
	},
	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.starred) {
			  this.$store.dispatch(ACTIONS.FETCH_STARRED, { start: this.$store.getters.starred[this.$store.getters.starred?.length - 1]?.id })
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
