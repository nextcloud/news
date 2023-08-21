<template>
	<div style="height: 100%">
		<div class="header">
			Unread
			<NcCounterBubble class="counter-bubble">
				{{ items.unreadCount }}
			</NcCounterBubble>
		</div>
		<div style="display: flex; height: 100%;">
			<VirtualScroll :reached-end="reachedEnd"
				:fetch-key="'unread'"
				style="width:100%"
				@load-more="fetchMore()">
				<template v-if="unread() && unread().length > 0">
					<template v-for="item in unread()">
						<FeedItemRow :key="item.id" :item="item" />
					</template>
				</template>
			</VirtualScroll>

			<div v-if="selected !== undefined" style="max-width: 50%; overflow-y: scroll;">
				<FeedItemDisplay :item="selected" />
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import VirtualScroll from './VirtualScroll.vue'
import FeedItemRow from './FeedItemRow.vue'
import FeedItemDisplay from './FeedItemDisplay.vue'

import { FeedItem } from '../types/FeedItem'
import { ACTIONS } from '../store'

export default Vue.extend({
	components: {
		NcCounterBubble,
		VirtualScroll,
		FeedItemRow,
		FeedItemDisplay,
	},
	data() {
		return {
			mounted: false,
			_unread: undefined,
		} as any
	},
	computed: {
		...mapState(['items']),
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.allItemsLoaded.unread !== undefined && this.$store.state.items.allItemsLoaded.unread
		},
		selected(): FeedItem | undefined {
			return this.$store.getters.selected
		},
	},
	mounted() {
		this.mounted = true
		this.$store.dispatch(ACTIONS.SET_SELECTED_ITEM, { id: undefined })
	},
	methods: {
		unread() {
			if (!this._unread) {
				if (this.$store.getters.unread.length > 0) {
					this._unread = this.$store.getters.unread
				}
			} else if (this.$store.getters.unread.length > (this._unread?.length)) {
				for (const item of this.$store.getters.unread) {
					if (this._unread.find((unread: FeedItem) => unread.id === item.id) === undefined) {
						this._unread.push(item)
					}
				}
			}

			return this._unread
		},
		async fetchMore() {
			if (this._unread && !this.$store.state.items.fetchingItems.unread) {
			  this.$store.dispatch(ACTIONS.FETCH_UNREAD, { start: this._unread[this._unread?.length - 1]?.id })
			}
		},
	},
})
</script>

<style>
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

.virtual-scroll {
	margin-top: 50px;
	border-top: 1px solid var(--color-border);
}
</style>
