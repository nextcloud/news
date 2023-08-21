<template>
	<div style="display: flex;">
		<div class="header">
			Starred
			<NcCounterBubble class="counter-bubble">
				{{ items.starredCount }}
			</NcCounterBubble>
		</div>
		<VirtualScroll :reached-end="reachedEnd"
			:fetch-key="'starred'"
			style="width: 100%;"
			@load-more="fetchMore()">
			<template v-if="starred && starred.length > 0">
				<template v-for="item in starred">
					<FeedItemComponent :key="item.id" :item="item" />
				</template>
			</template>
		</VirtualScroll>

		<div v-if="selected !== undefined" style="max-width: 50%; overflow-y: scroll;">
			<FeedItemDisplay :item="selected" />
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import VirtualScroll from './VirtualScroll.vue'
import FeedItemComponent from './FeedItem.vue'
import FeedItemDisplay from './FeedItemDisplay.vue'

import { FeedItem } from '../types/FeedItem'
import { ACTIONS } from '../store'

export default Vue.extend({
	components: {
		NcCounterBubble,
		VirtualScroll,
		FeedItemComponent,
		FeedItemDisplay,
	},
	data() {
		return {
			mounted: false,
		}
	},
	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			return this.$store.getters.starred
		},
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.starredLoaded
		},
		selected(): FeedItem | undefined {
			return this.$store.getters.selected
		},
	},
	mounted() {
		this.mounted = true
		this.$store.dispatch(ACTIONS.SET_SELECTED_ITEM, { id: undefined })
	},
	methods: {
		async fetchMore() {
			// TODO: fetch more starred
		},
	},
})
</script>

<style>
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

.virtual-scroll {
	margin-top: 50px;
	border-top: 1px solid var(--color-border);
}
</style>
