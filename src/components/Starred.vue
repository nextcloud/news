<template>
	<div>
		Starred Items:
		<div v-for="item in items.starredItems" :key="item.id">
			{{ item.title }}
		</div>
	</div>
</template>

<script lang="ts">
import { FEED_ITEM_ACTION_TYPES } from '../store/item'
import { mapState } from 'vuex'

export default {
	components: {
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
		await this.$store.dispatch(FEED_ITEM_ACTION_TYPES.FETCH_STARRED)
	},
	methods: {
		async fetchStarred() {
			// TODO
		},
	},
}
</script>
