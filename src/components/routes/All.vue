<template>
	<ContentTemplate
		v-if="!loading"
		:items="allItems"
		:fetch-key="'all'"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'All Articles') }}
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS } from '../../store/index.ts'
import { outOfScopeFilter } from '../../utils/itemFilter.ts'

export default defineComponent({
	name: 'RoutesAll',
	components: {
		ContentTemplate,
	},

	computed: {
		allItems(): FeedItem[] {
			return outOfScopeFilter(this.$store, this.$store.getters.allItems, 'all')
		},

		loading() {
			return this.$store.getters.loading
		},
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
	.counter-bubble {
		display: inline-block;
		vertical-align: sub;
		margin-inline-start: 10px;
	}
</style>
