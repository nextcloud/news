<template>
	<NcAppContent
		:layout="layout"
		:show-details="showDetails"
		:list-max-width="100"
		@update:show-details="showItem(false)">
		<template #list>
			<NcAppContentList>
				<FeedItemDisplayList
					ref="itemListElement"
					:items="items"
					:list-name="listName"
					:list-count="listCount"
					:fetch-key="fetchKey"
					role="region"
					:aria-label="t('news', 'Article list')"
					@show-details="showItem(true)"
					@mark-read="emit('mark-read')"
					@load-more="emit('load-more')">
					<template #header>
						<slot name="header" />
					</template>
				</FeedItemDisplayList>
			</NcAppContentList>
		</template>

		<NcAppContentDetails
			class="feed-item-content"
			role="region"
			:aria-label="t('news', 'Article details')">
			<div ref="contentElement" class="feed-item-content-wrapper">
				<FeedItemDisplay
					v-if="selectedFeedItem"
					:item="selectedFeedItem"
					:item-count="items.length"
					:item-index="currentIndex + 1"
					:fetch-key="fetchKey"
					@prev-item="previousItem"
					@next-item="nextItem"
					@show-details="showItem(false)" />
				<NcEmptyContent
					v-else
					style="margin-top: 20vh"
					:name="t('news', 'No article selected')"
					:description="t('news', 'Please select an article from the list.')">
					<template #icon>
						<TextIcon />
					</template>
					<template #action>
						<NcButton v-if="noSplitMode" variant="secondary" @click="showItem(false)">
							{{ t('news', 'Show all articles') }}
						</NcButton>
					</template>
				</NcEmptyContent>
			</div>
		</NcAppContentDetails>
	</NcAppContent>
</template>

<script setup lang="ts">

/**
 * This component uses vue's composition api format,
 * for more information, see https://vuejs.org/guide/extras/composition-api-faq.html
 */

import type { FeedItem } from '../types/FeedItem.ts'

import { getBuilder } from '@nextcloud/browser-storage'
import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile'
import {
	type PropType,

	computed, onBeforeMount, onBeforeUnmount, onMounted, onUpdated, ref, watch,
} from 'vue'
import { useStore } from 'vuex'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcAppContentDetails from '@nextcloud/vue/components/NcAppContentDetails'
import NcAppContentList from '@nextcloud/vue/components/NcAppContentList'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import TextIcon from 'vue-material-design-icons/Text.vue'
import FeedItemDisplay from './feed-display/FeedItemDisplay.vue'
import FeedItemDisplayList from './feed-display/FeedItemDisplayList.vue'
import { DISPLAY_MODE, SPLIT_MODE } from '../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../store/index.ts'

const props = defineProps({
	/**
	 * The items loaded for this view
	 */
	items: {
		type: Array as PropType<Array<FeedItem>>,
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
		required: false,
		default: '',
	},

	/**
	 * The counter value of the list
	 */
	listCount: {
		type: Number,
		required: false,
		default: 0,
	},
})

const emit = defineEmits<{
	(event: 'load-more'): void
	(event: 'mark-read'): void
}>()

const store = useStore()

const browserStorage = getBuilder('nextcloud-news').persist().build()

const isMobile = useIsMobile()

const showDetails = ref(false)
const initialSelection = ref(false)

const stopPageUpHotkey = ref(null)
const stopPageDownHotkey = ref(null)

const contentElement = ref()
const itemListElement = ref()

const displayMode = computed(() => {
	return store.getters.displaymode
})

const layout = computed(() => {
	switch (store.getters.splitmode) {
		case SPLIT_MODE.HORIZONTAL:
			return 'horizontal-split'
		case SPLIT_MODE.OFF:
			return 'no-split'
		case SPLIT_MODE.VERTICAL:
		default:
			return 'vertical-split'
	}
})

const selectedFeedItem = computed(() => {
	return store.getters.selected
})

const currentIndex = computed(() => {
	return selectedFeedItem.value ? props.items.findIndex((item: FeedItem) => item.id === selectedFeedItem.value.id) || 0 : -1
})

const allItemsLoaded = computed(() => {
	return store.state.items.allItemsLoaded[props.fetchKey] === true
})

const fetchingItems = computed(() => {
	return store.state.items.fetchingItems[props.fetchKey] === true
})

const itemReset = computed(() => {
	return store.state.items.newestItemId === 0
})

const noSplitMode = computed(() => {
	return (layout.value === 'no-split' || isMobile.value)
})

const detailsView = computed(() => {
	return noSplitMode.value && showDetails.value
})

/**
 * set showDetails value
 *
 * @param {boolean} value Show or hide item
 */
function showItem(value) {
	showDetails.value = value
	// store show details value in local storage
	if (noSplitMode.value) {
		browserStorage.setItem('news.show-details', value)
	}
	// scroll to selected item when closing details in no-split mode
	if (noSplitMode.value && !value) {
		itemListElement.value?.scrollToItem(currentIndex.value)
	}
}

/**
 * set selected item id, scroll element to top when in list view
 * and mark item as read
 *
 * @param item to select
 */
function selectItem(item: FeedItem) {
	store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: item.id, key: props.fetchKey })
	if (!noSplitMode.value || !showDetails.value) {
		itemListElement.value?.scrollToItem(currentIndex.value)
	}
	if (!item.keepUnread && item.unread) {
		store.dispatch(ACTIONS.MARK_READ, { item })
	}
}

/**
 * jump to previous list item
 *
 */
function previousItem() {
	// Jump to the previous item
	if (currentIndex.value > 0) {
		const previousItem = props.items[currentIndex.value - 1]
		selectItem(previousItem)
	}
}

/**
 * jump to next list item
 *
 */
function nextItem() {
	// Jump to the first item, if none was selected, otherwise jump to the next item
	if (props.items.length > 0 && currentIndex.value < props.items.length - 1) {
		const nextItem = props.items[currentIndex.value + 1]
		selectItem(nextItem)
	}
}

/**
 * enable PageUp/Down hotkeys with screen reader mode
 */
function enablePageHotkeys() {
	stopPageUpHotkey.value = useHotKey('PageUp', previousItem, { prevent: true })
	stopPageDownHotkey.value = useHotKey('PageDown', nextItem, { prevent: true })
}

/**
 * disable PageUp/Down hotkeys
 */
function disablePageHotkeys() {
	stopPageUpHotkey.value?.()
	stopPageUpHotkey.value = null
	stopPageDownHotkey.value?.()
	stopPageDownHotkey.value = null
}

watch(allItemsLoaded, (newVal) => {
	/*
	 * load new items if available and the details of the
	 * last item are currently displayed in no-split mode
	 */
	if (detailsView.value
		&& newVal === false
		&& currentIndex.value >= props.items.length - 1) {
		emit('load-more')
	}
})

/*
 * activate initialSelection when refreshing app
 * and in details view
 */
watch(itemReset, (newVal) => {
	if (detailsView.value && newVal === true) {
		initialSelection.value = true
	}
})

watch(selectedFeedItem, (newSelectedFeedItem) => {
	if (newSelectedFeedItem) {
		contentElement.value?.scrollTo(0, 0)
		/*
		 * load new items if available before reaching end of
		 * the list while showing details in no-split mode
		 */
		if (detailsView.value
			&& !allItemsLoaded.value
			&& currentIndex.value >= props.items.length - 5) {
			emit('load-more')
		}
	} else {
		if (!noSplitMode.value) {
			showItem(false)
		}
	}
})

watch(displayMode, (newDisplayMode) => {
	if (newDisplayMode === DISPLAY_MODE.SCREENREADER) {
		enablePageHotkeys()
	} else {
		disablePageHotkeys()
	}
})

onBeforeMount(() => {
	store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
})

onMounted(() => {
	// create shortcuts
	useHotKey(['p', 'k', 'ArrowLeft'], previousItem)
	useHotKey(['n', 'j', 'ArrowRight'], nextItem)
	if (displayMode.value === DISPLAY_MODE.SCREENREADER) {
		enablePageHotkeys()
	}
	// get show-details flag from browser storage
	if (noSplitMode.value) {
		showItem(browserStorage.getItem('news.show-details') === 'true')
		if (showDetails.value) {
			initialSelection.value = true
		}
	}
})

onBeforeUnmount(() => {
	disablePageHotkeys()
})

onUpdated(() => {
	// auto-select first item when in details view and initialSelection is set
	if (initialSelection.value && detailsView.value && !fetchingItems.value) {
		if (props.items.length > 0) {
			selectItem(props.items[0])
			initialSelection.value = false
		}
	}
})

</script>

<style scoped>
.feed-item-content {
	overflow:hidden;
	height: 100%
}

.feed-item-content-wrapper {
	height: 100%;
	overflow-y: scroll;
}
</style>
