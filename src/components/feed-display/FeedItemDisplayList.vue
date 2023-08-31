<template>
	<div>
		<div style="justify-content: right; display: flex">
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
		</div>
		<div class="feed-item-display-container">
			<VirtualScroll :reached-end="reachedEnd"
				:fetch-key="fetchKey"
				@load-more="fetchMore()">
				<template v-if="items && items.length > 0">
					<template v-for="item in filterSortedItems()">
						<FeedItemRow :key="item.id" :item="item" />
					</template>
				</template>
			</VirtualScroll>

			<div v-if="selected !== undefined" class="feed-item-container">
				<FeedItemDisplay :item="selected" />
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
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
import FeedItemDisplay from './FeedItemDisplay.vue'

import { FeedItem } from '../../types/FeedItem'

const DEFAULT_DISPLAY_LIST_CONFIG = {
	starFilter: true,
	unreadFilter: true,
}

export default Vue.extend({
	components: {
		VirtualScroll,
		FeedItemRow,
		FeedItemDisplay,
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
			type: Object,
			default: () => {
				return DEFAULT_DISPLAY_LIST_CONFIG
			},
		},
	},
	data() {
		return {
			mounted: false,

			// no filter to start
			filter: () => { return true as boolean },

			// Always want to sort by date (most recent first)
			sort: (a: FeedItem, b: FeedItem) => {
				if (a.pubDate > b.pubDate) {
					return -1
				} else {
					return 1
				}
			},
			cache: [] as FeedItem[] | undefined,
		}
	},
	computed: {
		selected(): FeedItem | undefined {
			return this.$store.getters.selected
		},
		reachedEnd(): boolean {
			return this.mounted && this.$store.state.items.allItemsLoaded[this.fetchKey] === true
		},
		cfg() {
			return _.defaults({ ...this.config }, DEFAULT_DISPLAY_LIST_CONFIG)
		},
	},
	mounted() {
		this.mounted = true
	},
	methods: {
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
		toggleFilter(filter: () => boolean) {
			if (this.filter === filter) {
				this.filter = this.noFilter
				if (filter === this.unreadFilter) {
					this.cache = undefined
				}
			} else {
				this.filter = filter as () => boolean
			}
		},
		filterSortedItems(): FeedItem[] {
			let response = [...this.items] as FeedItem[]

			// if we're filtering on unread, we want to cache the unread items when the user presses the filter button
			// that way when the user opens an item, it won't be removed from the displayed list of items (once it's no longer unread)
			if (this.filter === this.unreadFilter) {
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

			return response.sort(this.sort)
		},
	},
})
</script>

<style scoped>
	.virtual-scroll {
		border-top: 1px solid var(--color-border);
		width: 100%;
	}

	.feed-item-display-container {
		display: flex;
		height: 100%;
	}

	.feed-item-container {
		max-width: 50%;
		overflow-y: hidden;
	}

	.filter-container {
		padding: 5px;
	}
</style>
