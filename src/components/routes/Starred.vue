<template>
	<ContentTemplate
		v-if="!loading"
		:items="starred"
		:fetch-key="'starred'"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'Starred') }}
			<NcCounterBubble class="counter-bubble" :count="items.starredCount" />
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'RoutesStarred',
	components: {
		ContentTemplate,
		NcCounterBubble,
	},

	computed: {
		...mapState(['items']),
		starred(): FeedItem[] {
			return this.$store.getters.starred
		},

		loading() {
			return this.$store.getters.loading
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
		margin-inline-start: 10px;
	}
</style>
