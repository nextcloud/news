<template>
	<NcAppContent :show-details="showDetails"
		@update:showDetails="unselectItem()">
		<template #list>
			<NcAppContentList>
				<div class="header">
					<slot name="header" />
				</div>

				<FeedItemDisplayList :items="items"
					:fetch-key="fetchKey"
					:config="config"
					@load-more="emit('load-more')" />
			</NcAppContentList>
		</template>

		<NcAppContentDetails>
			<FeedItemDisplay v-if="selectedFeedItem" :item="selectedFeedItem" />
			<NcEmptyContent v-else
				:title="t('news', 'No article selected')"
				:description="t('news', 'Please select an article from the list...')">
				<template #icon>
					<TextIcon />
				</template>
			</NcEmptyContent>
		</NcAppContentDetails>
	</NcAppContent>
</template>

<script setup lang="ts">

/**
 * This component uses vue's composition api format,
 * for more information, see https://vuejs.org/guide/extras/composition-api-faq.html
 */

import { type PropType, computed, ref, watch } from 'vue'

import itemStore from '../store/item'

import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import NcAppContentList from '@nextcloud/vue/dist/Components/NcAppContentList'
import NcAppContentDetails from '@nextcloud/vue/dist/Components/NcAppContentDetails'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent'

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
	config: {
		type: Object,
	},
})

const emit = defineEmits<{(event: 'load-more'): void}>()

const showDetails = ref(false)

const selectedFeedItem = computed(() => {
	return itemStore.getters.selected(itemStore.state)
})

watch(selectedFeedItem, (newSelectedFeedItem) => {
	if (newSelectedFeedItem) {
		showDetails.value = true
	} else {
		showDetails.value = false
	}
})

/**
 * Unselect a list item.
 *
 */
function unselectItem() {
	itemStore.mutations.SET_SELECTED_ITEM(
		itemStore.state,
		{ id: undefined },
	)
}

</script>

<style scoped>

.header {
	padding-left: 50px;
	position: absolute;
	top: 1em;
	font-weight: 700;
}

</style>
