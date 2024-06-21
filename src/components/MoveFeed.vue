<template>
	<NcModal @close="$emit('close')">
		<div class="modal__content">
			<h2>{{ t("news", "Move feed to folder") }}</h2>
			<div class="form-group">
				<NcSelect v-if="folders"
					v-model="folder"
					:options="folders"
					:placeholder="'-- ' + t('news', 'No folder') + ' --'"
					required
					track-by="id"
					label="name" />
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

import Vue from 'vue'

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'

import { Folder } from '../types/Folder'
import { ACTIONS } from '../store'

type MoveFeedState = {
	folder?: Folder;
};

export default Vue.extend({
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
	data: (): MoveFeedState => {
		return {
			folder: undefined,
		}
	},
	computed: {
		folders(): Folder[] {
			return this.$store.state.folders.folders
		},
		disableMoveFeed(): boolean {
			console.log('feed', this.feed)
			console.log('this.folder', this.folder)
			return (this.folder !== undefined && this.folder.id === this.feed.folderId)
		},
	},
	methods: {
		/**
		 * Move a Feed via the Vuex Store
		 */
		async moveFeed() {
			const data = {
				feedId: this.feed.id,
				folderId: this.folder ? this.folder.id : null,
			}
			await this.$store.dispatch(ACTIONS.MOVE_FEED, data)
			await this.reloadFeeds()

			this.$emit('close')
		},
		async reloadFeeds() {
			// Clear feeds and folders
			const currentState = this.$store.state
			const newState = {
				...currentState,
				folders: {
					folders: [],
				},
				feeds: {
					feeds: [],
				},
			}
			this.$store.replaceState(newState)

			// Fetch feeds and folders
			await this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
			await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		},
	},
})

</script>

<style scoped>
.invalid {
	border: 1px solid rgb(251, 72, 72) !important;
}
.modal__content {
	margin: 50px;
}

.modal__content h2 {
	text-align: center;
}

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
