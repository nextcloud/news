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
				:disabled="disableMoveFeed || moving"
				@click="moveFeed()">
				{{ t("news", "Move") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script lang="ts">

import type { Folder } from '../types/Folder.ts'

import { showError } from '@nextcloud/dialogs'
import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import { ACTIONS } from '../store/index.ts'

type MoveFeedState = {
	folder: Folder | null
	moving: boolean
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
		 * The feed to move
		 */
		feed: {
			type: Object,
			required: false,
			default: () => {
				return { url: '' }
			},
		},

		feeds: {
			type: Array,
			required: false,
			default: () => [],
		},
	},

	emits: {
		close: () => true,
	},

	data: (): MoveFeedState => {
		return {
			folder: null,
			moving: false,
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
			return Array.isArray(this.feeds) && this.feeds.length > 0
		},

		disableMoveFeed(): boolean {
			if (this.isBatchMove) {
				return false
			}
			return (this.folder && this.folder.id === this.feed.folderId)
		},
	},

	methods: {
		async pauseBetweenBatchRequests() {
			await new Promise((resolve) => setTimeout(resolve, this.batchRequestDelay))
		},

		/**
		 * Move a Feed via the Vuex Store
		 */
		async moveFeed() {
			this.moving = true
			const folderId = this.folder ? this.folder.id : 0

			if (this.isBatchMove) {
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
					await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
				} finally {
					this.moving = false
				}

				if (failedMoves > 0) {
					showError(t('news', 'Some selected feeds could not be moved. Please try again later or check your connection.'))
					return
				}

				this.$emit('close')
				return
			}

			const data = {
				feedId: this.feed.id,
				folderId,
			}
			let response
			try {
				response = await this.$store.dispatch(ACTIONS.MOVE_FEED, data)
			} finally {
				this.moving = false
			}
			if (!response?.status || response.status < 200 || response.status >= 300) {
				showError(t('news', 'Unable to move feed. Please try again later or check your connection.'))
				return
			}
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)

			this.$emit('close')
		},
	},
})

</script>
