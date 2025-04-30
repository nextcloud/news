<!--
  - Copyright (c) 2022. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->
<script>
import Vue from 'vue'
import _ from 'lodash'

import ItemSkeleton from './ItemSkeleton.vue'
import { ACTIONS } from '../../store'

const GRID_ITEM_HEIGHT = 200 + 10

export default Vue.extend({
	name: 'VirtualScroll',
	props: {
		fetchKey: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			viewport: { width: 0, height: 0 },
			scrollTop: 0,
			scrollHeight: 500,
			initialLoadingSkeleton: false,
			initialLoadingTimeout: null,
			elementToShow: null,
			elementToFocus: null,
			debouncedMarkRead: null,
		}
	},
	computed: {
		reachedEnd: {
			cache: false,
			get() {
				return this.$store.state.items.allItemsLoaded[this.fetchKey] === true
			},
		},
		fetching: {
			cache: false,
			get() {
				return this.$store.state.items.fetchingItems[this.fetchKey]
			},
		},
		displayMode: {
			cache: false,
			get() {
				return this.$store.getters.displaymode
			},
		},
	},
	watch: {
		fetchKey: {
			handler() {
				this.scrollTop = 0
				this._seenItems = new Map()
			},
			immediate: true,
		},
	},
	created() {
		this._lastRendered = null
		this._lowerPaddingItems = 0
		this.debouncedMarkRead = _.debounce(this.markReadOnScroll, 500)
	},
	mounted() {
		this.onScroll()
		window.addEventListener('resize', this.onScroll)
	},
	destroyed() {
		window.removeEventListener('resize', this.onScroll)
	},
	updated() {
		if (!this.$store.getters.preventReadOnScroll) {
			this.addToSeen(this._lastRendered)
		}
	},
	methods: {
		addToSeen(children) {
			if (children) {
				children.forEach((child) => {
					if (!this._seenItems.has(child.key) && child.componentOptions.propsData.item.unread) {
						this._seenItems.set(child.key, { offset: child.elm.offsetTop, item: child.componentOptions.propsData.item })
					}
				})
			}
		},
		markReadOnScroll() {
			for (const [key, value] of this._seenItems) {
				if (this.scrollTop > value.offset) {
					const item = value.item
					if (!item.keepUnread && item.unread) {
						this.$store.dispatch(ACTIONS.MARK_READ, { item })
					}
					this._seenItems.delete(key)
				}
			}
		},
		onScroll() {
			this.scrollTop = this.$el.scrollTop
			this.scrollHeight = this.$el.scrollHeight
			this.loadMore()

			if (!this.$store.getters.preventReadOnScroll) {
				this.debouncedMarkRead()
			}
		},
		loadMore() {
			if (this._lowerPaddingItems === 0) {
				if (!this.reachedEnd && !this.fetching) {
					this.$emit('load-more')
				}
			}
		},
		showElement(element) {
			this.elementToShow = element
		},
	},
	render(h) {
		let children = []
		let renderedItems = 0
		let upperPaddingItems = 0
		let lowerPaddingItems = 0
		const itemHeight = this.displayMode === '1' ? 44 : 111
		const padding = GRID_ITEM_HEIGHT
		if (this.$slots.default && this.$el && this.$el.getBoundingClientRect) {
			const childComponents = this.$slots.default.filter(child => !!child.componentOptions)
			const viewport = this.$el.getBoundingClientRect()
			renderedItems = Math.floor((viewport.height + padding + padding) / itemHeight)
			upperPaddingItems = Math.floor(Math.max(this.scrollTop - padding, 0) / itemHeight)
			children = childComponents.slice(upperPaddingItems, upperPaddingItems + renderedItems)
			renderedItems = children.length
			lowerPaddingItems = Math.max(childComponents.length - upperPaddingItems - renderedItems, 0)
			this._lowerPaddingItems = lowerPaddingItems
			this._lastRendered = children

		}

		if (lowerPaddingItems === 0) {
			if (upperPaddingItems + renderedItems + lowerPaddingItems === 0) {
				if (!this.initialLoadingSkeleton) {
					// The first 350ms don't display skeletons
					this.initialLoadingTimeout = setTimeout(() => {
						this.initialLoadingSkeleton = true
						this.$forceUpdate()
					}, 350)
					return h('div', { class: 'virtual-scroll' })
				}
			}

			children = [...children, ...Array(40).fill(0).map(() =>
				h(ItemSkeleton),
			)]
		}

		if (upperPaddingItems + renderedItems + lowerPaddingItems > 0) {
			this.initialLoadingSkeleton = false
			if (this.initialLoadingTimeout) {
				clearTimeout(this.initialLoadingTimeout)
			}
		}

		const scrollTop = this.scrollTop
		this.$nextTick(() => {
			if (this.elementToShow) {
				// Workaround for buggy scroll with screen readers.
				// Remember currently selected item to focus on next tick
				if (this.displayMode === '2') {
					this.elementToFocus = this.elementToShow
				}
				this.elementToShow.scrollIntoView({ behavior: 'auto', block: 'start' })
				this.elementToShow = null
			} else {
				this.$el.scrollTop = scrollTop
			}
			// Focus title link in article to emulate structural heading navigation
			// with screen readers
			if (this.elementToFocus) {
				const titleLink = this.elementToFocus.querySelector('a')
				if (titleLink) {
					titleLink.focus()
				}
			}
		})

		return h('div', {
			class: 'virtual-scroll',
			on: { scroll: () => this.onScroll() },
		},
		[
			h('div', { class: 'upper-padding', style: { height: Math.max((upperPaddingItems) * itemHeight, 0) + 'px' } }),
			h('div', {
				class: 'container-window',
				style: { height: Math.max((renderedItems) * itemHeight, 0) + 'px' },
				attrs: { role: 'feed' },
			}, children),
			h('div', { class: 'lower-padding', style: { height: Math.max((lowerPaddingItems) * itemHeight, 0) + 'px' } }),
		])
	},
})
</script>

<style scoped>
.virtual-scroll {
	height: calc(100vh - 50px - 50px - 10px);
	position: relative;
	overflow-y: scroll;
}

.container-window::after {
	content: '';
	display: block;
	/* Subtract the height of the Nextcloud and Feed header. */
	height: calc(100vh - 50px - 54px);
	background-repeat: no-repeat;
}
</style>
