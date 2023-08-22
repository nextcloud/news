<template>
	<div style="height: 100%">
		<div class="header">
			Unread
			<NcCounterBubble class="counter-bubble">
				{{ items.unreadCount }}
			</NcCounterBubble>
		</div>

		<FeedItemDisplayList :items="unread()" :fetch-key="'starred'" @load-more="fetchMore()" />
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'

import FeedItemDisplayList from './FeedItemDisplayList.vue'

import { FeedItem } from '../types/FeedItem'
import { ACTIONS } from '../store'

type UnreadItemState = {
	_unread?: FeedItem[]
}

export default Vue.extend({
	components: {
		NcCounterBubble,
		FeedItemDisplayList,
	},
	data() {
		return {
			_unread: undefined,
		} as UnreadItemState
	},
	computed: {
		...mapState(['items']),
	},
	created() {
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
