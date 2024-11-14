<template>
	<div class="feed-item-display-list">
		<div class="header">
			<div class="header-content">
				<slot name="header" />
			</div>

			<button v-shortkey="['arrowleft']" class="hidden" @shortkey="jumpToPreviousItem">
				Prev
			</button>
			<button v-shortkey="['k']" class="hidden" @shortkey="jumpToPreviousItem">
				Prev
			</button>
			<button v-shortkey="['p']" class="hidden" @shortkey="jumpToPreviousItem">
				Prev
			</button>
			<button v-shortkey="['arrowright']" class="hidden" @shortkey="jumpToNextItem">
				Next
			</button>
			<button v-shortkey="['j']" class="hidden" @shortkey="jumpToNextItem">
				Next
			</button>
			<button v-shortkey="['n']" class="hidden" @shortkey="jumpToNextItem">
				Next
			</button>
			<button v-shortkey="['r']" class="hidden" @shortkey="refreshFeedList">
				Refresh
			</button>
		</div>
		<div class="feed-item-display-container">
			<VirtualScroll ref="virtualScroll"
				:fetch-key="fetchKey"
				@load-more="fetchMore()">
				<template v-if="items && items.length > 0">
					<template v-for="item in filteredItemcache">
						<FeedItemRow :key="item.id"
							:ref="'feedItemRow' + item.id"
							:item="item"
							:class="{ 'active': selectedItem && selectedItem.id === item.id }" />
					</template>
				</template>
			</VirtualScroll>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import _ from 'lodash'

import VirtualScroll from './VirtualScroll.vue'
import FeedItemRow from './FeedItemRow.vue'

import { FeedItem } from '../../types/FeedItem'
import { FEED_ORDER } from '../../dataservices/feed.service'
import { ACTIONS } from '../../store'

export default Vue.extend({
	components: {
		VirtualScroll,
		FeedItemRow,
	},
	props: {
		items: {
			type: Array<FeedItem>,
			required: true,
		},
		fetchKey: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			mounted: false,

			// Determine the sorting order
			sort: (a: FeedItem, b: FeedItem) => {
				if (a.id > b.id) {
					return this.listOrdering ? 1 : -1
				} else {
					return this.listOrdering ? -1 : 1
				}
			},
			cache: [] as FeedItem[] | undefined,
			filteredItemcache: [] as FeedItem,
			selectedItem: undefined as FeedItem | undefined,
			debouncedClickItem: null,
			listOrdering: this.getListOrdering(),
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
		isLoading() {
			return this.$store.getters.loading
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
				if (this.listOrdering === false) {
					this.$store.dispatch(ACTIONS.RESET_LAST_ITEM_LOADED)
				}
				this.cache = undefined
			},
			immediate: true,
		},
		// rebuild filtered item list only when items has changed
		items: {
			handler() {
				this.refreshItemList()
			},
			immediate: true,
			deep: false,
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
				// refresh the list with the new ordering
				this.refreshItemList()
			}
		},
		// showAll has changed rebuild item list
		changedShowAll() {
			this.$refs.virtualScroll.scrollTop = 0
			this.cache = undefined
			this.refreshItemList()
		},
	},
	mounted() {
		this.mounted = true
		this.setupDebouncedClick()
	},
	methods: {
		async refreshFeedList() {
			// with ordering newest>oldest complete refresh of item list needed
			if (!this.listOrdering) {
				this.$store.dispatch(ACTIONS.RESET_LAST_ITEM_LOADED)
				this.$refs.virtualScroll.scrollTop = 0
				// make sure the first items from this ordering are loaded
				this.fetchMore()
				this.cache = undefined
				this.refreshItemList()
			}
			// sync feed counter with backend
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		},
		refreshItemList() {
			if (this.items.length > 0) {
				this.filteredItemcache = this.filterSortedItems()
			}
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
			if (this.fetchKey !== 'starred' && !this.$store.getters.showAll) {
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
				element.click()
				const virtualScroll = this.$refs.virtualScroll
				virtualScroll.showElement(element)

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
	}

	.header-content {
		flex-grow: 1;
		padding-left: 50px;
		font-weight: 700;
	}

	.filter-container {
		padding: 5px;
	}
</style>
