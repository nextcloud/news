<template>
	<NcModal @close="$emit('close')">
		<div class="modal__content">
			<div class="form-group">
				<NcSelect v-if="folders"
					v-model="folder"
					:options="folders"
					:placeholder="'-- ' + t('news', 'No folder') + ' --'"
					required
					track-by="id"
					label="name"
					style="width: 90%;" />
			</div>
			<NcButton :wide="true"
				type="primary"
				:disabled="disableMoveFeed"
				@click="moveFeed()">
				{{ t("news", "Move") }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script lang="ts">

import { defineComponent } from 'vue'

import NcModal from '@nextcloud/vue/components/NcModal'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'

import { Folder } from '../types/Folder'
import { ACTIONS } from '../store'

type MoveFeedState = {
	folder?: Folder;
};

export default defineComponent({
	components: {
		NcModal,
		NcButton,
		NcSelect,
	},
	props: {
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

<style scoped>
.invalid {
	border: 1px solid rgb(251, 72, 72) !important;
}

.modal__content {
	margin: 16px;
}

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
