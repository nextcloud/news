<template>
	<ContentTemplate
		:items="recentItems"
		fetch-key="recent">
		<template #header>
			{{ t('news', 'Recently viewed') }}
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import ContentTemplate from '../ContentTemplate.vue'

export default defineComponent({
	name: 'RoutesRecent',
	components: {
		ContentTemplate,
	},

	computed: {
		recentItems(): FeedItem[] {
			const items = this.$store.getters.allItems
			const recentItemIds = this.$store.getters.recentItemIds
			const map = new Map(items.map((item) => [item.id, item]))
			return recentItemIds.map((id) => map.get(id)).filter(Boolean)
		},
	},
})
</script>
