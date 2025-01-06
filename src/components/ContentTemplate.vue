<template>
	<NcAppContent :layout="layout"
		:show-details="showDetails"
		:mobile-layout="'horizontal-split'"
		:list-max-width="100"
		@update:showDetails="showItem(false)">
		<template #list>
			<NcAppContentList>
				<FeedItemDisplayList :items="items"
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

		<NcAppContentDetails class="feed-item-content"
			role="region"
			:aria-label="t('news', 'Article details')">
			<div ref="contentElement" class="feed-item-content-wrapper">
				<FeedItemDisplay v-if="selectedFeedItem"
					:item="selectedFeedItem"
					:hide-item-nav="layout === 'no-split'"
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
	switch (appStore.getters.splitmode(appStore.state)) {
	case '1':
		return 'horizontal-split'
	case '2':
		return 'no-split'
	case '0':
	default:
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
