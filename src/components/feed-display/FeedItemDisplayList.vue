<template>
	<div class="feed-item-display-list">
		<div class="header">
			<div class="header-content">
				<slot name="header" />
			</div>
		</div>
		<div class="feed-item-display-container">
			<VirtualScroll
				ref="virtualScroll"
				:fetch-key="fetchKey"
				@load-more="fetchMore()">
				<template v-if="filteredItemcache && filteredItemcache.length > 0">
					<template v-for="(item, index) in filteredItemcache">
						<FeedItemDisplay
							v-if="screenReaderMode"
							:key="item.id"
							:ref="'feedItemRow' + item.id"
							:item-count="filteredItemcache.length"
							:item-index="index + 1"
							:item="item"
							:class="{ active: selectedItem && selectedItem.id === item.id }"
							@click-item="clickItem(item)" />
						<FeedItemRow
							v-else
							:key="item.id"
							:ref="'feedItemRow' + item.id"
							:item-count="filteredItemcache.length"
							:item-index="index + 1"
							:item="item"
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
import _ from 'lodash'
import { defineComponent } from 'vue'
import FeedItemDisplay from './FeedItemDisplay.vue'
import FeedItemRow from './FeedItemRow.vue'
import VirtualScroll from './VirtualScroll.vue'
import { DISPLAY_MODE, FEED_ORDER, SPLIT_MODE } from '../../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	components: {
		VirtualScroll,
		FeedItemDisplay,
		FeedItemRow,
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
	},

	emits: {
		'load-more': () => true,
		'mark-read': () => true,
		'show-details': () => true,
	},

	data() {
		return {
			// Determine the sorting order
			sort: (a: FeedItem, b: FeedItem) => {
				if (a.id > b.id) {
					return this.listOrdering ? 1 : -1
				} else {
					return this.listOrdering ? -1 : 1
				}
			},

			cache: [] as FeedItem[] | undefined,
			selectedItem: undefined as FeedItem | undefined,
			debouncedClickItem: null,
			listOrdering: this.getListOrdering(),
			stopPrevItemHotkey: null,
			stopNextItemHotkey: null,
		}
	},

	computed: {
		getSelectedItem() {
			return this.$store.getters.selected
		},

		syncNeeded() {
			return this.$store.state.items.syncNeeded
		},

		changedFeedOrdering() {
			if (this.fetchKey.startsWith('feed-')) {
				return this.$store.state.feeds.ordering[this.fetchKey]
			}
			return 0
		},

		changedGlobalOrdering() {
			return this.$store.getters.oldestFirst
		},

		changedOrdering() {
			return {
				feedOrdering: this.changedFeedOrdering,
				globalOrdering: this.changedGlobalOrdering,
			}
		},

		changedShowAll() {
			return this.$store.getters.showAll
		},

		filteredItemcache() {
			return this.items.length > 0 ? this.filterSortedItems() : []
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

		// clear cache on route change
		fetchKey: {
			handler() {
				this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
				if (this.listOrdering === false) {
					this.$store.dispatch(ACTIONS.RESET_LAST_ITEM_LOADED)
				}
				this.cache = undefined
			},

			immediate: true,
		},

		// ordering has changed rebuild item list
		changedOrdering() {
			const newListOrdering = this.getListOrdering()
			if (newListOrdering !== this.listOrdering) {
				this.listOrdering = newListOrdering
				this.$refs.virtualScroll.scrollTop = 0
				// make sure the first items from this ordering are loaded
				this.fetchMore()
				this.cache = undefined
			}
		},

		// showAll has changed rebuild item list
		changedShowAll() {
			this.$refs.virtualScroll.scrollTop = 0
			this.cache = undefined
		},
	},

	created() {
		// create shortcuts
		this.enableNavHotkeys()
		useHotKey('r', this.refreshApp)
		useHotKey('a', this.markRead, { shift: true })
		useHotKey(['s', 'l', 'i'], this.toggleStarred)
		useHotKey('u', this.toggleRead)
		useHotKey('o', this.openUrl)
		// use e/Enter only when split mode is of
		if (this.splitModeOff && !this.screenReaderMode) {
			useHotKey('e', this.showDetails)
			useHotKey('Enter', this.showDetails)
		}
		// use PageUP/PageDown only in screen reader mode
		if (this.screenReaderMode) {
			useHotKey('PageUp', this.jumpToPreviousItem, { prevent: true })
			useHotKey('PageDown', this.jumpToNextItem, { prevent: true })
		}
	},

	mounted() {
		this.setupDebouncedClick()
	},

	unmounted() {
		this.disableNavHotkeys()
	},

	methods: {
		async refreshApp() {
			this.$refs.virtualScroll.scrollTop = 0
			this.cache = undefined
			// remove all loaded items
			this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
			// refetch feeds
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		},

		getListOrdering(): boolean {
			// all routes expect feeds use global ordering
			if (!this.fetchKey.startsWith('feed-')) {
				return this.$store.getters.oldestFirst
			}
			let oldestFirst
			switch (this.$store.state.feeds.ordering[this.fetchKey]) {
				case FEED_ORDER.OLDEST:
					oldestFirst = true
					break
				case FEED_ORDER.NEWEST:
					oldestFirst = false
					break
				case FEED_ORDER.DEFAULT:
				default:
					oldestFirst = this.$store.getters.oldestFirst
			}
			return oldestFirst
		},

		fetchMore() {
			this.$emit('load-more')
		},

		markRead() {
			this.$emit('mark-read')
		},

		disableNavHotkeys() {
			if (this.stopPrevItemHotkey) {
				this.stopPrevItemHotkey()
			}
			if (this.stopNextItemHotkey) {
				this.stopNextItemHotkey()
			}
		},

		enableNavHotkeys() {
			this.disableNavHotkeys()
			this.stopPrevItemHotkey = useHotKey(['p', 'k', 'ArrowLeft'], this.jumpToPreviousItem)
			this.stopNextItemHotkey = useHotKey(['n', 'j', 'ArrowRight'], this.jumpToNextItem)
		},

		showDetails() {
			/*
			 * disable nav keys when showing details in no-split-mode
			 * proper navigation (fetchMore, scroll to last item when closed)
			 * isn't implemented yet
			 */
			if (this.splitModeOff) {
				this.disableNavHotkeys()
			}
			this.$emit('show-details')
		},

		unreadFilter(item: FeedItem): boolean {
			return item.unread
		},

		outOfScopeFilter(item: FeedItem): boolean {
			const lastItemLoaded = this.$store.state.items.lastItemLoaded[this.fetchKey]
			return (this.listOrdering ? lastItemLoaded >= item.id : lastItemLoaded <= item.id)
		},

		filterSortedItems(): FeedItem[] {
			let response = [...this.items] as FeedItem[]

			// if we're filtering on unread, we want to cache the unread items when the user presses the filter button
			// that way when the user opens an item, it won't be removed from the displayed list of items (once it's no longer unread)
			if (this.fetchKey === 'unread'
				|| (!this.$store.getters.showAll
					&& this.fetchKey !== 'starred'
					&& this.fetchKey !== 'all')) {
				if (!this.cache) {
					if (this.items.length > 0) {
						this.cache = this.items.filter(this.unreadFilter)
					}
				} else if (this.items.length > (this.cache?.length)) {
					for (const item of this.items) {
						if (item.unread && this.cache.find((unread: FeedItem) => unread.id === item.id) === undefined) {
							this.cache.push(item)
						}
					}
				}
				response = [...this.cache as FeedItem[]]
			}

			// filter items that are already loaded but do not yet match the current view
			if (this.$store.state.items.lastItemLoaded[this.fetchKey] > 0) {
				response = response.filter(this.outOfScopeFilter)
			}
			return response.sort(this.sort)
		},

		// debounce clicks to prevent multiple api calls when on the end of the actual loaded list
		setupDebouncedClick() {
			this.debouncedClickItem = _.debounce((Item) => {
				this.clickItem(Item)
			}, 20, { leading: true })
		},

		// Trigger the click event programmatically to benefit from the item handling inside the FeedItemRow component
		clickItem(item: FeedItem) {
			if (!item) {
				return
			}

			const refName = 'feedItemRow' + item.id
			const ref = this.$refs[refName]
			// Make linter happy
			const componentInstance = Array.isArray(ref) && ref.length && ref.length > 0 ? ref[0] : undefined
			const element = componentInstance ? componentInstance.$el : undefined

			if (element) {
				const virtualScroll = this.$refs.virtualScroll
				virtualScroll.showElement(element)
			}

			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: item.id })
			if (!item.keepUnread && item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item })
			}
		},

		currentIndex(items: FeedItem[]): number {
			return this.selectedItem ? items.findIndex((item: FeedItem) => item.id === this.selectedItem.id) || 0 : -1
		},

		jumpToPreviousItem() {
			const items = this.filteredItemcache
			let currentIndex = this.currentIndex(items)
			// Prepare to jump to the first item, if none was selected
			if (currentIndex === -1) {
				currentIndex = 1
			}
			// Jump to the previous item
			if (currentIndex > 0) {
				const previousItem = items[currentIndex - 1]
				this.debouncedClickItem(previousItem)
			}
		},

		jumpToNextItem() {
			const items = this.filteredItemcache
			const currentIndex = this.currentIndex(items)
			// Jump to the first item, if none was selected, otherwise jump to the next item
			if (currentIndex === -1 || (currentIndex < items.length - 1)) {
				const nextItem = items[currentIndex + 1]
				this.debouncedClickItem(nextItem)
			}
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
		display: flex;
		align-items: center;
		justify-content: right;
		height: 54px;
		min-height: 54px;
	}

	.header-content {
		flex-grow: 1;
		padding-inline-start: 52px;
		font-weight: 700;
	}
</style>
