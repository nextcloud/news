<template>
	<ContentTemplate
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
import { mapState } from 'vuex'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'RoutesAll',
	components: {
		ContentTemplate,
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
	.counter-bubble {
		display: inline-block;
		vertical-align: sub;
		margin-left: 10px;
	}
</style>
