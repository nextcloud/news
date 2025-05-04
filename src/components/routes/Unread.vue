<template>
	<ContentTemplate
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
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'RoutesUnread',
	components: {
		ContentTemplate,
		NcCounterBubble,
	},

	data() {
		return {
			unreadCache: undefined,
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
	},

	watch: {
		newestItemId(clearCache) {
			if (clearCache) {
				this.unreadCache = undefined
			}
		},

		// need cache so we aren't always removing items when they get read
		'$store.getters.unread': {
			handler(newItems) {
				const cachedItems = this.unreadCache ?? []

				const cachedItemIds = new Set(cachedItems.map((item) => item.id))
				const newUnreadCache = [...cachedItems]

				for (const item of newItems) {
					if (!cachedItemIds.has(item.id)) {
						newUnreadCache.push(item)
					}
				}

				this.unreadCache = newUnreadCache
			},

			immediate: true,
		},
	},

	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		if (this.unread === undefined) {
			this.$store.dispatch(ACTIONS.FETCH_UNREAD)
		}
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
		margin-left: 10px;
	}
</style>
