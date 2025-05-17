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
					:item-index="currentIndex"
					@prev-item="jumpToPreviousItem"
					@next-item="jumpToNextItem"
					@show-details="showItem(false)" />
				<NcEmptyContent
					v-else
					style="margin-top: 20vh"
					:name="t('news', 'No article selected')"
					:description="t('news', 'Please select an article from the list...')">
					<template #icon>
						<TextIcon />
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

import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile'
import {
	type PropType,

	computed, ref, watch,
} from 'vue'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcAppContentDetails from '@nextcloud/vue/components/NcAppContentDetails'
import NcAppContentList from '@nextcloud/vue/components/NcAppContentList'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import TextIcon from 'vue-material-design-icons/Text.vue'
import FeedItemDisplay from './feed-display/FeedItemDisplay.vue'
import FeedItemDisplayList from './feed-display/FeedItemDisplayList.vue'
import { SPLIT_MODE } from '../enums/index.ts'
import appStore from '../store/app.ts'
import itemStore from '../store/item.ts'

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
})

const emit = defineEmits<{
	(event: 'load-more'): void
	(event: 'mark-read'): void
}>()

const isMobile = useIsMobile()

const showDetails = ref(false)

const contentElement = ref()

const itemListElement = ref()

const layout = computed(() => {
	switch (appStore.getters.splitmode(appStore.state)) {
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
	return itemStore.getters.selected(itemStore.state)
})

const allItemsLoaded = computed(() => {
	return itemStore.state.allItemsLoaded[props.fetchKey] === true
})

const currentIndex = computed(() => {
	return itemListElement.value?.currentIndex() + 1
})

const noSplitMode = computed(() => {
	return (layout.value === 'no-split' || isMobile.value)
})

watch(allItemsLoaded, (newVal) => {
	/*
	 * load new items if available and the details of the
	 * last item are currently displayed in no-split mode
	 */
	if (noSplitMode.value
		&& newVal === false
		&& showDetails.value
		&& currentIndex.value >= props.items.length - 1) {
		emit('load-more')
	}
})

watch(selectedFeedItem, (newSelectedFeedItem) => {
	if (newSelectedFeedItem) {
		contentElement.value?.scrollTo(0, 0)
		/*
		 * load new items if available before reaching end of
		 * the list while showing details in no-split mode
		 */
		if (noSplitMode.value
			&& showDetails.value
			&& !allItemsLoaded.value
			&& currentIndex.value >= props.items.length - 5) {
			emit('load-more')
		}
	} else {
		showItem(false)
	}
})

/**
 * set showDetails value
 *
 * @param {boolean} value Show or hide item
 */
function showItem(value) {
	showDetails.value = value
	// scroll to selected item when closing details in no-split mode
	if (noSplitMode.value && !value) {
		itemListElement.value?.scrollToCurrentItem()
	}
}

/**
 * jump to previous list item
 *
 */
function jumpToPreviousItem() {
	itemListElement.value?.jumpToPreviousItem()
}

/**
 * jump to next list item
 *
 */
function jumpToNextItem() {
	itemListElement.value?.jumpToNextItem()
}

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
