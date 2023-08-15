<template>
	<div style="margin-top: 40px;">
		<VirtualScroll :reached-end="items.starredLoaded">
			<template v-if="items.starredItems && items.starredItems.length > 0">
				<template v-for="item in items.starredItems">
					<FeedItem :key="item.id" :item="item" />
				</template>
			</template>
		</VirtualScroll>
	</div>
</template>

<script lang="ts">
import { mapState } from 'vuex'
import FeedItem from './FeedItem.vue'
import VirtualScroll from './VirtualScroll.vue'

export default {
	components: {
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
