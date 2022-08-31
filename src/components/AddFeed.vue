<template>
	<NcModal @close="$emit('close')" :title="t('news', 'Add new feed')">
		<div class="add-feed">
			<h2>{{ t('news', 'Add new feed') }}</h2>
			<form ng-submit="Navigation.createFeed(Navigation.feed)" name="feedform">
				<NcTextField type="text"
					:value.sync="feedUrl"
					:label="t('news', 'Web address')"
					placeholder="https://..."
					:labelVisible="true"
					pattern="[^\s]+"
					required
					autofocus
					:error="feedAlreadyExists" />

				<!-- select a folder -->
				<NcCheckboxRadioSwitch :checked.sync="createNewFolder" type="switch">
					{{ t("news", "New folder") }}?
				</NcCheckboxRadioSwitch>

				<div v-if="!createNewFolder">
					<label for="name">{{ t('news', 'Folder name') }}</label>
					<NcMultiselect v-model="folder"
						:options="folders"
						track-by="id"
						label="name" />
				</div>

				<!-- add a folder -->
				<NcTextField v-if="createNewFolder"
					:label="t('news', 'Folder name')"
					:labelVisible="true"
					:value.sync="folderName"
					required />

				<!-- basic auth -->
				<NcCheckboxRadioSwitch :checked.sync="withBasicAuth" type="switch">
					{{ t("news", "Credentials") }}?
				</NcCheckboxRadioSwitch>

				<fieldset v-if="withBasicAuth" class="add-feed-basicauth">
					<p class="warning">
						{{
							t(
								"news",
								"HTTP Basic Auth credentials must be stored unencrypted! Everyone with access to the server or database will be able to access them!"
							)
						}}>
					</p>
					<NcTextField type="text"
						:label="t('news', 'Username')"
						:labelVisible="true"
						:value="username"
						name="user"
						autofocus />

					<NcTextField type="password"
						:label="t('news', 'Password')"
						:labelVisible="true"
						:value="password"
						name="password"
						autocomplete="new-password" />
				</fieldset>

				<NcCheckboxRadioSwitch :checked.sync="autoDiscover" type="switch">
					{{ t("news", "Auto discover Feed") }}?
				</NcCheckboxRadioSwitch>

				<NcButton :wide="true"
					type="primary"
					ng-disabled="
					Navigation.feedUrlExists(Navigation.feed.url) ||
							(
								Navigation.showNewFolder &&
								Navigation.folderNameExists(folder.name)
							)"
					@click="addFeed()">
					{{ t("news", "Subscribe") }}
				</NcButton>
			</form>
		</div>
	</NcModal>
</template>

<script>
/* eslint-disable vue/require-prop-type-constructor */

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'

export default {
	name: 'AddFeed',
	components: {
		NcModal,
		NcCheckboxRadioSwitch,
		NcButton,
		NcMultiselect,
		NcTextField,
	},
	props: {
		feed: '',
	},
	emits: ['close'],
	data() {
		return {
			feedUrl: '',
			folderName: '',
			password: '',
			username: '',
			folder: {},
			autoDiscover: true,
			createNewFolder: false,
			withBasicAuth: false,
			feedAlreadyExists: false,
		}
	},
	computed: {
		folders() {
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
			this.$store.dispatch('addFeed', {
				feedReq: {
					url: this.feed,
					folder: this.folder,
					autoDiscover: true,
				},
			})
		},
	},
}
</script>

<style scoped lang="scss">
.add-feed {
	padding: 1rem;
	form {
		display: flex;
		flex-direction: column;
		gap: 1rem;
	}
}
input {
    width: 100%
}

.multiselect {
    width: 100%
}
</style>
