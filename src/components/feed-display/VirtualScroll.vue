<!--
  - Copyright (c) 2022. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->
<script>
import Vue from 'vue'

import ItemSkeleton from './ItemSkeleton.vue'

const GRID_ITEM_HEIGHT = 200 + 10
// const GRID_ITEM_WIDTH = 250 + 10
const LIST_ITEM_HEIGHT = 110 + 1

export default Vue.extend({
	name: 'VirtualScroll',
	props: {
		reachedEnd: {
			type: Boolean,
			required: true,
		},
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
		}
	},
	computed: {
		fetching: {
			cache: false,
			get() {
				return this.$store.state.items.fetchingItems[this.fetchKey]
			},
		},
	},
	watch: {
		fetchKey() {
			this.scrollTop = 0
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
		onScroll() {
			this.scrollTop = this.$el.scrollTop
			this.scrollHeight = this.$el.scrollHeight
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
		let itemHeight = 1
		const padding = GRID_ITEM_HEIGHT
		if (this.$slots.default && this.$el && this.$el.getBoundingClientRect) {
			const childComponents = this.$slots.default.filter(child => !!child.componentOptions)
			const viewport = this.$el.getBoundingClientRect()
			itemHeight = LIST_ITEM_HEIGHT
			renderedItems = Math.floor((viewport.height + padding + padding) / itemHeight)
			upperPaddingItems = Math.floor(Math.max(this.scrollTop - padding, 0) / itemHeight)
			children = childComponents.slice(upperPaddingItems, upperPaddingItems + renderedItems)
			renderedItems = children.length
			lowerPaddingItems = Math.max(childComponents.length - upperPaddingItems - renderedItems, 0)
		}

		if (!this.reachedEnd && lowerPaddingItems === 0) {
			if (!this.fetching) {
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
				this.elementToShow.scrollIntoView({ behavior: 'auto', block: 'nearest' })
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
</style>
