<template>
	<NcModal @close="$emit('close')">
		<div id="new-feed" news-add-feed="Navigation.feed">
			<form ng-submit="Navigation.createFeed(Navigation.feed)"
				ng-init="Navigation.feed.autoDiscover=true"
				name="feedform">
				<fieldset ng-disabled="Navigation.addingFeed" style="padding: 16px">
					<input type="text"
						:value="feed"
						ng-model="Navigation.feed.url"
						ng-class="{'ng-invalid':
												!Navigation.addingFeed &&
												Navigation.feedUrlExists(Navigation.feed.url)
											}"
						:placeholder="t('news', 'Web address')"
						name="address"
						pattern="[^\s]+"
						required
						autofocus>

					<p class="error"
						ng-show="!Navigation.addingFeed &&
											Navigation.feedUrlExists(Navigation.feed.url)">
						{{ t("news", "Feed exists already!") }}
					</p>

					<!-- select a folder -->
					<NcCheckboxRadioSwitch :checked.sync="createNewFolder" type="switch">
						{{ t("news", "New folder") }}?
					</NcCheckboxRadioSwitch>

					<NcMultiselect v-if="!createNewFolder && folders"
						v-model="folder"
						:options="folders"
						track-by="id"
						label="name" />

					<!-- add a folder -->
					<input v-if="createNewFolder"
						type="text"
						ng-model="Navigation.feed.newFolder"
						ng-class="{'ng-invalid':
														!Navigation.addingFeed &&
														!Navigation.addingFeed &&
														Navigation.showNewFolder &&
														Navigation.folderNameExists(Navigation.feed.newFolder)
											}"
						:placeholder="t('news', 'Folder name')"
						name="folderName"
						style="width: 90%"
						required>

					<p class="error"
						ng-show="!Navigation.addingFeed &&
											Navigation.folderNameExists(Navigation.feed.newFolder)">
						{{ t("news", "Folder exists already!") }}
					</p>

					<!-- basic auth -->

					<NcCheckboxRadioSwitch :checked.sync="withBasicAuth" type="switch">
						{{ t("news", "Credentials") }}?
					</NcCheckboxRadioSwitch>

					<div v-if="withBasicAuth" class="add-feed-basicauth">
						<p class="warning">
							{{
								t(
									"news",
									"HTTP Basic Auth credentials must be stored unencrypted! Everyone with access to the server or database will be able to access them!"
								)
							}}
						</p>
						<input type="text"
							ng-model="Navigation.feed.user"
							:placeholder="t('news', 'Username')"
							name="user"
							autofocus>

						<input type="password"
							ng-model="Navigation.feed.password"
							:placeholder="t('news', 'Password')"
							name="password"
							autocomplete="new-password">
					</div>

					<NcCheckboxRadioSwitch :checked.sync="autoDiscover" type="switch">
						{{ t("news", "Auto discover Feed") }}?
					</NcCheckboxRadioSwitch>

					<NcButton :wide="true"
						type="primary"
						ng-disabled="Navigation.feedUrlExists(Navigation.feed.url) ||
													(
														Navigation.showNewFolder &&
														Navigation.folderNameExists(folder.name)
													)"
						@click="addFeed()">
						{{ t("news", "Subscribe") }}
					</NcButton>
				</fieldset>
			</form>
		</div>
	</NcModal>
</template>

<script lang="ts">

import Vue from 'vue'

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect.js'

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import { ACTIONS } from '../store'

type AddFeedState = {
	folder: Folder;
	autoDiscover: boolean;
	createNewFolder: boolean;
	withBasicAuth: boolean;

	// from props
	feed?: Feed;
};

export default Vue.extend({
	components: {
		NcModal,
		NcCheckboxRadioSwitch,
		NcButton,
		NcMultiselect,
	},
	props: {
		feed: {
			type: String,
			default: '',
		},
	},
	data: (): AddFeedState => {
		return {
			folder: { name: '' } as Folder,
			autoDiscover: true,
			createNewFolder: false,
			withBasicAuth: false,
		}
	},
	computed: {
		folders(): Folder[] {
			return this.$store.state.folders
		},
	},
	methods: {
		newFolder() {
			this.createNewFolder = true
		},
		abortNewFolder() {
			this.createNewFolder = false
		},
		addFeed() {
			this.$store.dispatch(ACTIONS.ADD_FEED, {
				feedReq: {
					url: this.feed,
					folder: this.folder,
					autoDiscover: true,
				},
			})
		},
	},
})

</script>

<style scoped>
input {
    width: 100%
}

.multiselect {
    width: 100%
}
</style>
