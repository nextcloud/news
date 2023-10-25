<template>
	<NcAppContent>
		<template #list>
			<div class="header">
				{{ t('news', 'Unread Articles') }}
				<NcCounterBubble class="counter-bubble">
					{{ items.unreadCount }}
				</NcCounterBubble>
			</div>

			<FeedItemDisplayList v-if="unread()"
				:items="unread()"
				:fetch-key="'unread'"
				:config="{ unreadFilter: false }"
				@load-more="fetchMore()" />
		</template>
	</NcAppContent>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from '../feed-display/FeedItemDisplayList.vue'

import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

type UnreadItemState = {
	// need cache so we aren't always removing items when they get read
	unreadCache?: FeedItem[]
}

export default Vue.extend({
	components: {
		NcAppContent,
		NcCounterBubble,
		FeedItemDisplayList,
	},
	data() {
		return {
			unreadCache: undefined,
		} as UnreadItemState
	},
	computed: {
		...mapState(['items']),
	},
	created() {
		this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		if (this.unread() === undefined) {
			this.$store.dispatch(ACTIONS.FETCH_UNREAD)
		}
	},
	methods: {
		unread() {
			if (!this.unreadCache) {
				if (this.$store.getters.unread.length > 0) {
					this.unreadCache = this.$store.getters.unread
				}
			} else if (this.$store.getters.unread.length > (this.unreadCache?.length)) {
				for (const item of this.$store.getters.unread) {
					if (this.unreadCache.find((unread: FeedItem) => unread.id === item.id) === undefined) {
						this.unreadCache.push(item)
					}
				}
			}

			return this.unreadCache
		},
		async fetchMore() {
			if (this.unreadCache && !this.$store.state.items.fetchingItems.unread) {
			  this.$store.dispatch(ACTIONS.FETCH_UNREAD)
			}
		},
	},
})
</script>

<style scoped>
	.header {
		padding-left: 50px;
		position: absolute;
		top: 1em;
		font-weight: 700;
	}

	.counter-bubble {
		display: inline-block;
		vertical-align: sub;
		margin-left: 10px;
	}
</style>
