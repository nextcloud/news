<template>
	<div>
		<div style="padding-left: 50px; position: absolute; top: 1em; font-weight: 700;">
			Starred
			<NcCounterBubble style="display: inline-block; vertical-align: sub; margin-left: 10px;">
				{{ items.starredCount }}
			</NcCounterBubble>
		</div>
		<VirtualScroll :reached-end="items.starredLoaded" style="margin-top: 50px; border-top: 1px solid var(--color-border);">
			<template v-if="items.starredItems && items.starredItems.length > 0">
				<template v-for="item in items.starredItems">
					<FeedItem :key="item.id" :item="item" />
				</template>
			</template>
		</VirtualScroll>
	</div>
</template>

<script lang="ts">
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import { mapState } from 'vuex'

import VirtualScroll from './VirtualScroll.vue'
import FeedItem from './FeedItem.vue'

export default {
	components: {
		NcCounterBubble,
		VirtualScroll,
		FeedItem,
	},
	props: {

	},
	computed: {
		...mapState(['items']),
		// starred(): any[] {
		// return this.$store.state.items.starredItems
		// },
	},
	async created() {
		// TODO: ?
	},
	methods: {
		async fetchStarred() {
			// TODO
		},
	},
}
</script>
