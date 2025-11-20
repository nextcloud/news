<template>
	<div class="feed-item-display-list">
		<div class="header">
			<div class="header-content">
				<div class="header-text">
					{{ listName }}
				</div>
				<NcCounterBubble v-if="listCount > 0" class="header-counter" :count="listCount" />
			</div>
			<NcButton
				v-if="!isMobile"
				class="header-button"
				:title="t('news', 'Refresh list')"
				variant="tertiary"
				@click="refreshApp">
				<template #icon>
					<RefreshIcon :size="20" />
				</template>
			</NcButton>
		</div>
		<div class="feed-item-display-container">
			<VirtualScroll
				ref="virtualScroll"
				:fetch-key="fetchKey"
				@load-more="fetchMore()">
				<template v-if="items && items.length > 0">
					<template v-for="(item, index) in items">
						<FeedItemDisplay
							v-if="screenReaderMode"
							:key="item.id"
							:ref="'feedItemRow' + item.id"
							:item-count="items.length"
							:item-index="index + 1"
							:item="item"
							:fetch-key="fetchKey"
							:class="{ active: selectedItem && selectedItem.id === item.id }" />
						<FeedItemRow
							v-else
							:key="item.id"
							:ref="'feedItemRow' + item.id"
							:item-count="items.length"
							:item-index="index + 1"
							:item="item"
							:fetch-key="fetchKey"
							:class="{ active: selectedItem && selectedItem.id === item.id }"
							@show-details="showDetails" />
					</template>
				</template>
			</VirtualScroll>
		</div>
	</div>
</template>

<script lang="ts">
import type { FeedItem } from '../../types/FeedItem.ts'

import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile'
import { useSwipe } from '@vueuse/core'
import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import RefreshIcon from 'vue-material-design-icons/Refresh.vue'
import FeedItemDisplay from './FeedItemDisplay.vue'
import FeedItemRow from './FeedItemRow.vue'
import VirtualScroll from './VirtualScroll.vue'
import { DISPLAY_MODE, SPLIT_MODE } from '../../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	components: {
		VirtualScroll,
		FeedItemDisplay,
		FeedItemRow,
		NcButton,
		NcCounterBubble,
		RefreshIcon,
	},

	props: {
		/**
		 * The items loaded for this view
		 */
		items: {
			type: Array<FeedItem>,
			required: true,
		},

		/**
		 * The name of the view e.g. all, unread, feed-10
		 */
		fetchKey: {
			type: String,
			required: true,
		},

		/**
		 * The name of the list
		 */
		listName: {
			type: String,
			required: true,
		},

		/**
		 * The counter value of the list
		 */
		listCount: {
			type: Number,
			required: true,
		},
	},

	emits: {
		'load-more': () => true,
		'mark-read': () => true,
		'show-details': () => true,
	},

	setup() {
		return {
			isMobile: useIsMobile(),
		}
	},

	data() {
		return {
			selectedItem: undefined as FeedItem | undefined,
			swiping: {},
		}
	},

	computed: {
		getSelectedItem() {
			return this.$store.getters.selected
		},

		syncNeeded() {
			return this.$store.state.items.syncNeeded
		},

		lastItemLoaded() {
			return this.$store.state.items.lastItemLoaded[this.fetchKey]
		},

		isLoading() {
			return this.$store.getters.loading
		},

		screenReaderMode() {
			return this.$store.getters.displaymode === DISPLAY_MODE.SCREENREADER
		},

		splitModeOff() {
			return this.$store.getters.splitmode === SPLIT_MODE.OFF
		},
	},

	watch: {
		async syncNeeded(needSync) {
			if (!this.isLoading && needSync) {
				await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
			}
		},

		getSelectedItem(newVal) {
			this.selectedItem = newVal
		},

		// reset scroll position on item reset
		lastItemLoaded(newVal) {
			if (newVal === undefined) {
				this.$refs.virtualScroll.scrollTop = 0
			}
		},
	},

	created() {
		// create shortcuts
		useHotKey('r', this.refreshApp)
		useHotKey('a', this.markRead, { shift: true })
		useHotKey(['s', 'l', 'i'], this.toggleStarred)
		useHotKey('u', this.toggleRead)
		useHotKey('o', this.openUrl)
		// use e/Enter only when split mode is off
		if (this.splitModeOff && !this.screenReaderMode) {
			useHotKey('e', this.showDetails)
			useHotKey('Enter', this.showDetails)
		}
	},

	mounted() {
		this.swiping = useSwipe(this.$el, {
			onSwipeEnd: this.handleSwipe,
		})
	},

	methods: {
		/**
		 * handle the swipe event
		 *
		 * @param {TouchEvent} e The touch event
		 * @param {import('@vueuse/core').SwipeDirection} direction The swipe direction of the event
		 */
		handleSwipe(e, direction) {
			const minSwipeY = 70
			const touchZone = 300
			if (Math.abs(this.swiping.lengthY) > minSwipeY) {
				if (this.swiping.coordsStart.y < (touchZone / 2) && direction === 'down') {
					this.refreshApp()
				}
			}
		},

		/**
		 * reset item states and sync feed counters
		 */
		async refreshApp() {
			this.$refs.virtualScroll.scrollTop = 0
			this.$refs.virtualScroll._seenItems = new Map()
			// remove all loaded items
			this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
			// refetch feeds
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		},

		fetchMore() {
			this.$emit('load-more')
		},

		markRead() {
			this.$emit('mark-read')
		},

		showDetails() {
			this.$emit('show-details')
		},

		scrollToItem(currentIndex) {
			this.$refs.virtualScroll.scrollToItem(currentIndex)
		},

		toggleStarred(): void {
			const item = this.selectedItem
			if (item) {
				this.$store.dispatch(item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item })
			}
		},

		toggleRead(): void {
			const item = this.selectedItem
			if (!item) {
				return
			}
			if (!item.keepUnread && item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item })
			} else {
				this.$store.dispatch(ACTIONS.MARK_UNREAD, { item })
			}
		},

		openUrl(): void {
			const item = this.selectedItem
			// Open the item url in a new tab
			if (item && item.url) {
				window.open(item.url, '_blank')
			}
		},
	},
})
</script>

<style scoped>
	.feed-item-display-list {
		display: flex;
		flex-direction: column;
		overflow-y: hidden;
		height: 100%;
	}

	.virtual-scroll {
		border-top: 1px solid var(--color-border);
		width: 100%;
	}

	.feed-item-display-container {
		display: flex;
		height: 100%;
		overflow-y: hidden;
	}

	@media only screen and (min-width: 320px) {
		.virtual-scroll {
			flex: 1 1.2 auto;
		}

		.feed-item-display-container {
			flex-direction: column;
		}
	}

	@media only screen and (min-width: 768px) {
		.feed-item-display-container {
			flex-direction: row;
		}
	}

	.header {
		height: 54px;
		min-height: 54px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		min-width: 0;
		margin-inline-start: 44px;
		margin-inline-end: 10px;
	}

	.header-content {
		display: flex;
		align-items: center;
		min-width: 0;
		flex: 1;
		overflow: hidden;
		white-space: nowrap;
	}

	.header-text {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		flex-shrink: 1;
		margin-inline-end: 6px;
	}

	.header-counter {
		flex-shrink: 0;
	}

	.header-button {
		flex-shrink: 0;
	}

</style>
