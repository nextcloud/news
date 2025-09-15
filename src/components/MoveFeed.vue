<template>
	<NcDialog
		:name="t('news', 'Move feed')"
		size="small"
		@close="$emit('close')">
		<template #default>
			<NcSelect
				v-if="folders"
				v-model="folder"
				:options="folders"
				:placeholder="'-- ' + t('news', 'No folder') + ' --'"
				required
				:input-label="t('news', 'Please select the new folder')"
				label="name"
				style="width: 100%;" />
		</template>
		<template #actions>
			<NcButton
				:wide="true"
				variant="primary"
				:disabled="disableMoveFeed"
				@click="moveFeed()">
				{{ t("news", "Move") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script lang="ts">

import type { Folder } from '../types/Folder.ts'

import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import { ACTIONS } from '../store/index.ts'

type MoveFeedState = {
	folder?: Folder
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
	},

	emits: {
		close: () => true,
	},

	data: (): MoveFeedState => {
		return {
			folder: null,
		}
	},

	computed: {
		folders(): Folder[] {
			return this.$store.state.folders.folders
		},

		disableMoveFeed(): boolean {
			return (this.folder && this.folder.id === this.feed.folderId)
		},
	},

	methods: {
		/**
		 * Move a Feed via the Vuex Store
		 */
		async moveFeed() {
			const data = {
				feedId: this.feed.id,
				folderId: this.folder ? this.folder.id : 0,
			}
			await this.$store.dispatch(ACTIONS.MOVE_FEED, data)
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)

			this.$emit('close')
		},
	},
})

</script>
