<template>
	<NcAppContent :layout="layout"
		:show-details="showDetails"
		:list-max-width="100"
		@update:showDetails="showItem(false)">
		<template #list>
			<NcAppContentList>
				<FeedItemDisplayList :items="items"
					:fetch-key="fetchKey"
					@show-details="showItem(true)"
					@load-more="emit('load-more')">
					<template #header>
						<slot name="header" />
					</template>
				</FeedItemDisplayList>
			</NcAppContentList>
		</template>

		<NcAppContentDetails class="feed-item-content">
			<div ref="contentElement" class="feed-item-content-wrapper">
				<FeedItemDisplay v-if="selectedFeedItem"
					:item="selectedFeedItem"
					@show-details="showItem(false)" />
				<NcEmptyContent v-else
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

import { type PropType, computed, ref, watch } from 'vue'

import appStore from '../store/app'
import itemStore from '../store/item'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList.js'
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'

import TextIcon from 'vue-material-design-icons/Text.vue'

import { FeedItem } from '../types/FeedItem'

import FeedItemDisplayList from './feed-display/FeedItemDisplayList.vue'
import FeedItemDisplay from './feed-display/FeedItemDisplay.vue'

defineProps({
	items: {
		type: Array as PropType<Array<FeedItem>>,
		required: true,
	},
	fetchKey: {
		type: String,
		required: true,
	},
})

const emit = defineEmits<{(event: 'load-more'): void}>()

const showDetails = ref(false)

const contentElement = ref()

const layout = computed(() => {
	if (appStore.getters.compact(appStore.state)) {
		return appStore.getters.compactExpand(appStore.state) ? 'horizontal-split' : 'no-split'
	} else {
		return 'vertical-split'
	}
})

const selectedFeedItem = computed(() => {
	return itemStore.getters.selected(itemStore.state)
})

watch(selectedFeedItem, (newSelectedFeedItem) => {
	if (newSelectedFeedItem) {
		contentElement.value?.scrollTo(0, 0)
	} else {
		showItem(false)
	}
})

/**
 * set showDetails value
 *
 * @param {boolean} value Show or hide item
 *
 */
function showItem(value) {
	showDetails.value = value
}

</script>

<style>
.feed-item-content {
	overflow:hidden;
	height: 100%
}

.feed-item-content-wrapper {
	height: 100%;
	overflow-y: scroll;
}
</style>
