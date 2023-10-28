<template>
	<ContentTemplate :items="allItems"
		:fetch-key="'all'"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'All Articles') }}
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import ContentTemplate from '../ContentTemplate.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
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
