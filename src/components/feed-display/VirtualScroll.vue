<!--
  - Copyright (c) 2022. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->
<script>
import Vue from 'vue'

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
			checkMarkRead: true,
			seenItems: new Map(),
			lastRendered: null,
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
		compactMode: {
			cache: false,
			get() {
				return this.$store.getters.compact
			},
		},
	},
	watch: {
		fetchKey: {
			handler() {
				this.scrollTop = 0
				this.seenItems = new Map()
			},
			immediate: true,
		},
		lastRendered() {
			if (!this.$store.getters.preventReadOnScroll) {
				this.addToSeen(this.lastRendered)
			}
		},
	},
	mounted() {
		this.onScroll()
		window.addEventListener('resize', this.onScroll)
	},
	destroyed() {
		window.removeEventListener('resize', this.onScroll)
	},
	methods: {
		addToSeen(children) {
			if (children) {
				children.forEach((child) => {
					if (!this.seenItems.has(child.key) && child.componentOptions.propsData.item.unread) {
						this.seenItems.set(child.key, { offset: child.elm.offsetTop, item: child.componentOptions.propsData.item })
					}
				})
			}
		},
		markReadOnScroll() {
			for (const [key, value] of this.seenItems) {
				if (this.scrollTop > value.offset) {
					const item = value.item
					if (!item.keepUnread && item.unread) {
						this.$store.dispatch(ACTIONS.MARK_READ, { item })
					}
					this.seenItems.delete(key)
				}
			}
		},
		onScroll() {
			this.scrollTop = this.$el.scrollTop
			this.scrollHeight = this.$el.scrollHeight

			if (!this.$store.getters.preventReadOnScroll) {
				if (this.checkMarkRead) {
					this.checkMarkRead = false
					setTimeout(() => {
						this.markReadOnScroll()
						this.checkMarkRead = true
				        }, 500)
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
		const itemHeight = this.compactMode ? 44 : 111
		const padding = GRID_ITEM_HEIGHT
		if (this.$slots.default && this.$el && this.$el.getBoundingClientRect) {
			const childComponents = this.$slots.default.filter(child => !!child.componentOptions)
			const viewport = this.$el.getBoundingClientRect()
			renderedItems = Math.floor((viewport.height + padding + padding) / itemHeight)
			upperPaddingItems = Math.floor(Math.max(this.scrollTop - padding, 0) / itemHeight)
			children = childComponents.slice(upperPaddingItems, upperPaddingItems + renderedItems)
			renderedItems = children.length
			lowerPaddingItems = Math.max(childComponents.length - upperPaddingItems - renderedItems, 0)
			this.lastRendered = children
		}

		if (lowerPaddingItems === 0) {
			if (!this.reachedEnd && !this.fetching) {
				this.$emit('load-more')
			}
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
				this.elementToShow.scrollIntoView({ behavior: 'auto', block: 'start' })
				this.elementToShow = null
			} else {
				this.$el.scrollTop = scrollTop
			}
		})

		return h('div', {
			class: 'virtual-scroll',
			on: { scroll: () => this.onScroll() },
		},
		[
			h('div', { class: 'upper-padding', style: { height: Math.max((upperPaddingItems) * itemHeight, 0) + 'px' } }),
			h('div', { class: 'container-window', style: { height: Math.max((renderedItems) * itemHeight, 0) + 'px' } }, children),
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
