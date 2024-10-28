<template>
	<div class="feed-item-display-list">
		<div class="header">
			<div class="header-content">
				<slot name="header" />
			</div>

			<NcActions class="filter-container" :force-menu="true">
				<template #icon>
					<FilterIcon />
				</template>
				<NcActionButton v-if="cfg.unreadFilter" @click="toggleFilter(unreadFilter)">
					<template #default>
						{{ t("news", "Unread") }}
					</template>
					<template #icon>
						<EyeIcon v-if="filter !== unreadFilter" />
						<EyeCheckIcon v-if="filter === unreadFilter" />
					</template>
				</NcActionButton>
				<NcActionButton v-if="cfg.starFilter" @click="toggleFilter(starFilter)">
					<template #default>
						{{ t("news", "Starred") }}
					</template>
					<template #icon>
						<StarIcon v-if="filter !== starFilter" />
						<StarCheckIcon v-if="filter === starFilter" />
					</template>
				</NcActionButton>
			</NcActions>
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
		</div>
		<div class="feed-item-display-container">
			<VirtualScroll ref="virtualScroll"
				:reached-end="reachedEnd"
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
import Vue, { type PropType } from 'vue'
import _ from 'lodash'

import FilterIcon from 'vue-material-design-icons/Filter.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import StarCheckIcon from 'vue-material-design-icons/StarCheck.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'

import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import VirtualScroll from './VirtualScroll.vue'
import FeedItemRow from './FeedItemRow.vue'

import { FeedItem } from '../../types/FeedItem'
import { FEED_ORDER } from '../../dataservices/feed.service'

const DEFAULT_DISPLAY_LIST_CONFIG = {
	starFilter: true,
	unreadFilter: true,
	ordering: FEED_ORDER.NEWEST,
}

export type Config = {
	unreadFilter: boolean;
	starFilter: boolean;
	ordering: number;
}

export default Vue.extend({
	components: {
		VirtualScroll,
		FeedItemRow,
		FilterIcon,
		StarIcon,
		StarCheckIcon,
		EyeIcon,
		EyeCheckIcon,
		NcActions,
		NcActionButton,
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
		config: {
			type: Object as PropType<Config>,
			default: () => {
				return DEFAULT_DISPLAY_LIST_CONFIG
			},
		},
	},
	data() {
		return {
			mounted: false,

			// Show unread items at start
			filter: () => { return this.unreadFilter },

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
		}
	},
	computed: {
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.allItemsLoaded[this.fetchKey] === true
		},
		cfg() {
			return _.defaults({ ...this.config }, DEFAULT_DISPLAY_LIST_CONFIG)
		},
		getSelectedItem() {
			return this.$store.getters.selected
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
	},
	watch: {
		getSelectedItem(newVal) {
			this.selectedItem = newVal
		},
		fetchKey() {
			this.listOrdering = this.getListOrdering()
			this.cache = undefined
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
			this.listOrdering = this.getListOrdering()
			// make sure the first items from this ordering are loaded
			this.fetchMore()
			this.cache = undefined
			// refresh the list with the new ordering
			this.refreshItemList()
			this.$refs.virtualScroll.scrollTop = 0
		},
		// showAll has changed rebuild item list
		changedShowAll() {
			this.cache = undefined
			this.refreshItemList()
		},
	},
	created() {
		this.listOrdering = this.getListOrdering()
		this.loadFilter()
	},
	mounted() {
		this.mounted = true
		this.setupDebouncedClick()
	},
	methods: {
		refreshItemList() {
			if (this.items.length > 0) {
				this.filteredItemcache = this.filterSortedItems()
			}
		},
		getListOrdering(): boolean {
			if (!this.fetchKey.startsWith('feed-')) {
				return this.$store.getters.oldestFirst
			}
			// this.config.ordering is only defined in feed route
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
		storeFilter() {
			try {
				let filterString = 'noFilter'

				if (this.filter === this.starFilter) {
					filterString = 'starFilter'
				} else if (this.filter === this.unreadFilter) {
					filterString = 'unreadFilter'
				}

				localStorage.setItem('news-filter', filterString)
			} catch (error) {
				console.error('Error saving filter to local storage:', error)
			}
		},
		loadFilter() {
			try {
				const filterString = localStorage.getItem('news-filter')

				if (filterString) {
					switch (filterString) {
					case 'starFilter':
						this.filter = this.starFilter
						break
					case 'unreadFilter':
						this.filter = this.unreadFilter
						break
					default:
						this.filter = this.noFilter
					}
				}
			} catch (error) {
				console.error('Error loading filter from local storage:', error)
			}
		},
		fetchMore() {
			this.$emit('load-more')
		},
		noFilter(): boolean {
			return true
		},
		starFilter(item: FeedItem): boolean {
			return item.starred
		},
		unreadFilter(item: FeedItem): boolean {
			return item.unread
		},
		outOfScopeFilter(item: FeedItem): boolean {
			const lastItemLoaded = this.$store.state.items.lastItemLoaded[this.fetchKey]
			return (this.listOrdering ? lastItemLoaded >= item.id : lastItemLoaded <= item.id)
		},
		toggleFilter(filter: (item: FeedItem) => boolean) {
			if (this.filter === filter) {
				this.filter = this.noFilter
				if (filter === this.unreadFilter) {
					this.cache = undefined
				}
			} else {
				this.filter = filter as () => boolean
			}

			this.storeFilter()
			this.refreshItemList()
		},
		filterSortedItems(): FeedItem[] {
			let response = [...this.items] as FeedItem[]

			let itemFilter = this.filter

			if ((this.fetchKey !== 'starred' && this.filter !== this.starFilter)
				&& !this.$store.getters.showAll) {
				itemFilter = this.unreadFilter
			}

			// if we're filtering on unread, we want to cache the unread items when the user presses the filter button
			// that way when the user opens an item, it won't be removed from the displayed list of items (once it's no longer unread)
			if (itemFilter === this.unreadFilter) {
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
			} else {
				response = response.filter(this.filter)
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
