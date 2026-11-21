<template>
	<NcDialog
		:name="dialogTitle"
		size="small"
		@close="$emit('close')">
		<template #default>
			<NcSelect
				v-if="folders"
				v-model="folder"
				:options="folders"
				:placeholder="'-- ' + t('news', 'No folder') + ' --'"
				required
				:inputLabel="t('news', 'Please select the new folder')"
				label="name"
				style="width: 100%;" />
		</template>
		<template #actions>
			<NcButton
				:wide="true"
				variant="primary"
				:disabled="disableMoveFeed || Boolean(movingToast)"
				@click="moveFeeds()">
				{{ t("news", "Move") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script lang="ts">

import type { Folder } from '../types/Folder.ts'

import { showError, showLoading } from '@nextcloud/dialogs'
import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import { ACTIONS } from '../store/index.ts'

type MoveFeedState = {
	folder: Folder | null
	movingToast: ReturnType<typeof showLoading> | null
	batchRequestDelay: number
}

export default defineComponent({
	components: {
		NcButton,
		NcDialog,
		NcSelect,
	},

	props: {
		/**
		 * The feeds to move
		 */
		feeds: {
			type: Array,
			required: true,
		},
	},

	emits: {
		close: () => true,
	},

	data: (): MoveFeedState => {
		return {
			folder: null,
			movingToast: null,
			batchRequestDelay: 150,
		}
	},

	computed: {
		folders(): Folder[] {
			return this.$store.state.folders.folders
		},

		dialogTitle(): string {
			return this.isBatchMove ? t('news', 'Move feeds') : t('news', 'Move feed')
		},

		isBatchMove(): boolean {
			return Array.isArray(this.feeds) && this.feeds.length > 1
		},

		disableMoveFeed(): boolean {
			if (this.isBatchMove) {
				return false
			}
			const firstFeed = this.feeds?.[0]
			if (!firstFeed) {
				return true
			}
			return (this.folder && this.folder.id === firstFeed.folderId)
		},
	},

	methods: {
		async pauseBetweenBatchRequests() {
			await new Promise((resolve) => setTimeout(resolve, this.batchRequestDelay))
		},

		/**
		 * Move a Feed via the Vuex Store
		 */
		async moveFeeds() {
			this.movingToast = showLoading(t('news', 'Moving feeds…'))
			const folderId = this.folder ? this.folder.id : 0

			let failedMoves = 0
			const feedsToMove = this.feeds.filter((feed) => (typeof feed.folderId === 'number' ? feed.folderId : 0) !== folderId)
			try {
				for (const [index, feed] of feedsToMove.entries()) {
					try {
						const response = await this.$store.dispatch(ACTIONS.MOVE_FEED, { feedId: feed.id, folderId })
						if (!response?.status || response.status < 200 || response.status >= 300) {
							failedMoves++
						}
					} catch {
						failedMoves++
					}

					if (index < feedsToMove.length - 1) {
						await this.pauseBetweenBatchRequests()
					}
				}
			} finally {
				this.movingToast?.hideToast()
				this.movingToast = null
			}

			if (failedMoves > 0) {
				if (feedsToMove.length === 1) {
					showError(t('news', 'Unable to move feed. Please try again later or check your connection.'))
					return
				} else {
					showError(t('news', 'Some selected feeds could not be moved. Please try again later or check your connection.'))
				}
			}
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)

			this.$emit('close')
		},
	},
})

</script>
