<template>
	<ContentTemplate
		v-if="!loading"
		:items="unread"
		:fetch-key="'unread'"
		@load-more="fetchMore()">
		<template #header>
			{{ t('news', 'Unread Articles') }}
			<NcCounterBubble class="counter-bubble" :count="items.unreadCount" />
		</template>
	</ContentTemplate>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import ContentTemplate from '../ContentTemplate.vue'
import { ACTIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'RoutesUnread',
	components: {
		ContentTemplate,
		NcCounterBubble,
	},

	data() {
		return {
			unreadCache: [],
		}
	},

	computed: {
		...mapState(['items']),
		newestItemId() {
			return this.$store.state.items.newestItemId === 0
		},

		unread(): FeedItem[] {
			return this.unreadCache ?? []
		},

		loading() {
			return this.$store.getters.loading
		},
	},

	watch: {
		newestItemId(clearCache) {
			if (clearCache) {
				this.unreadCache = []
			}
		},

		// need cache so we aren't always removing items when they get read
		'$store.getters.unread': {
			handler(newItems) {
				const cachedItemIds = new Set(this.unreadCache.map((item) => item.id))

				for (const item of newItems) {
					if (!cachedItemIds.has(item.id)) {
						this.unreadCache.push(item)
					}
				}
			},

			immediate: true,
		},
	},

	methods: {
		async fetchMore() {
			if (!this.$store.state.items.fetchingItems.unread) {
				this.$store.dispatch(ACTIONS.FETCH_UNREAD)
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
