<template>
	<div class="feed-item-display-container">
		<VirtualScroll :reached-end="reachedEnd"
			:fetch-key="fetchKey"
			@load-more="fetchMore()">
			<template v-if="items && items.length > 0">
				<template v-for="item in items">
					<FeedItemRow :key="item.id" :item="item" />
				</template>
			</template>
		</VirtualScroll>

		<div v-if="selected !== undefined">
			<FeedItemDisplay :item="selected" />
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'

import VirtualScroll from './VirtualScroll.vue'
import FeedItemRow from './FeedItemRow.vue'
import FeedItemDisplay from './FeedItemDisplay.vue'

import { FeedItem } from '../types/FeedItem'

type FeedItemDisplayListState = {
	mounted: boolean
}

export default Vue.extend({
	components: {
		VirtualScroll,
		FeedItemRow,
		FeedItemDisplay,
	},
	props: {
		items: {
			type: Array,
			required: true,
		},
		fetchKey: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			mounted: false,
		} as FeedItemDisplayListState
	},
	computed: {
		selected(): FeedItem | undefined {
			return this.$store.getters.selected
		},
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.allItemsLoaded[this.fetchKey] !== undefined && this.$store.state.items.allItemsLoaded[this.fetchKey]
		},
	},
	mounted() {
		this.mounted = true
	},
	methods: {
		fetchMore() {
			this.$emit('load-more')
		},
	},
})
</script>

<style scoped>
	.feed-item-display-container {
		display: flex;
		height: 100%;
	}

	.virtual-scroll {
		width: 100%;
	}

	.feed-item-container {
		max-width: 50%;
		overflow-y: scroll;
	}
</style>
