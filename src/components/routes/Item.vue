<template>
	<ContentTemplate
		v-if="!loading"
		:key="'item-' + itemId"
		:items="item ? [item] : []"
		fetchKey="item" />
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'RoutesItem',
	components: {
		ContentTemplate,
	},

	props: {
		/**
		 * ID of the item to display
		 */
		itemId: {
			type: String,
			required: true,
		},
	},

	computed: {
		item(): FeedItem | undefined {
			const items = this.$store.getters.allItems
			return items.find((item: FeedItem) => item.id === this.id)
		},

		id(): number {
			return Number(this.itemId)
		},

		loading() {
			return this.$store.getters.loading
		},

		oldestFirst() {
			return this.$store.getters.oldestFirst
		},
	},

	created() {
		this.fetchMore()
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.all) {
				this.$store.dispatch(ACTIONS.FETCH_ITEMS, { start: Math.max(1, this.id + (this.oldestFirst ? -1 : 1)), limit: 1 })
			}
		},
	},
})
</script>
