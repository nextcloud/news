<template>
	<div>
		<div class="header">
			Starred
			<NcCounterBubble class="counter-bubble">
				{{ items.starredCount }}
			</NcCounterBubble>
		</div>
		<VirtualScroll :reached-end="reachedEnd" @load-more="fetchMore()">
			<template v-if="items.starredItems && items.starredItems.length > 0">
				<template v-for="item in items.starredItems">
					<FeedItem :key="item.id" :item="item" />
				</template>
			</template>
		</VirtualScroll>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import VirtualScroll from './VirtualScroll.vue'
import FeedItem from './FeedItem.vue'

export default Vue.extend({
	components: {
		NcCounterBubble,
		VirtualScroll,
		FeedItem,
	},
	data() {
		return {
			mounted: false,
		}
	},
	computed: {
		...mapState(['items']),
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.starredLoaded
		},
	},
	mounted() {
		this.mounted = true
	},
	methods: {
		async fetchMore() {
			// TODO
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
