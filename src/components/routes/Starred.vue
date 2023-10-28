<template>
	<ContentTemplate :items="starred"
		:fetch-key="'starred'"
		:config="{ starFilter: false }"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'Starred') }}
			<NcCounterBubble class="counter-bubble">
				{{ items.starredCount }}
			</NcCounterBubble>
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import ContentTemplate from '../ContentTemplate.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
	components: {
		ContentTemplate,
		NcCounterBubble,
	},
	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			return this.$store.getters.starred
		},
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
	},
	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.starred) {
			  this.$store.dispatch(ACTIONS.FETCH_STARRED)
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
