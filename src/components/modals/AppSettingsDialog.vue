<template>
	<NcAppSettingsDialog
		v-model:open="showSettings"
		:show-navigation="true"
		:name="t('news', 'News settings')"
		@close="$emit('close')">
		<NcAppSettingsSection id="settings-general" :name="t('news', 'General')">
			<NcFormBox>
				<NcFormBoxSwitch
					v-model="preventReadOnScroll"
					:label="t('news', 'Disable mark read through scrolling')" />

				<NcFormBoxSwitch
					v-model="showAll"
					:label="t('news', 'Show all articles')" />

				<NcFormBoxSwitch
					v-model="oldestFirst"
					:label="t('news', 'Reverse ordering (oldest on top)')" />

				<NcFormBoxSwitch
					v-model="disableRefresh"
					:label="t('news', 'Disable automatic refresh')" />
			</NcFormBox>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="settings-display" :name="t('news', 'Appearance')">
			<NcRadioGroup
				v-model="displaymode"
				:label="t('news', 'Display mode')">
				<NcRadioGroupButton
					v-for="displayMode in displayModeOptions"
					:key="displayMode.id"
					:label="displayMode.name"
					:value="displayMode.id" />
			</NcRadioGroup>
			<NcRadioGroup
				v-model="splitmode"
				:label="t('news', 'Split mode')">
				<NcRadioGroupButton
					v-for="splitMode in splitModeOptions"
					:key="splitMode.id"
					:label="splitMode.name"
					:value="splitMode.id" />
			</NcRadioGroup>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="settings-opml" :name="t('news', 'Import') + '/' + t('news', 'Export')">
			<NcFormGroup :label="t('news', 'Abonnements (OPML)')">
				<div class="button-container">
					<NcButton
						aria-label="UploadOpml"
						:disabled="loading"
						@click="$refs.fileSelect.click()">
						<template #icon>
							<UploadIcon :size="20" />
						</template>
					</NcButton>

					<input
						ref="fileSelect"
						type="file"
						class="hidden"
						accept=".opml"
						@change="importOpml">

					<NcButton
						aria-label="DownloadOpml"
						:disabled="loading"
						@click="exportOpml">
						<template #icon>
							<DownloadIcon :size="20" />
						</template>
					</NcButton>
				</div>
				<NcNoteCard v-if="loading" type="info">
					<template #icon>
						<NcLoadingIcon size="20" />
					</template>
					<template #default>
						<h1>{{ t('news', 'Importing feeds') }}...{{ t('news', 'Please wait') }}</h1>
					</template>
				</NcNoteCard>
				<NcNoteCard v-else-if="uploadOpmlStatusMessage" :type="uploadOpmlStatusType" :text="uploadOpmlStatusMessage" />
			</NcFormGroup>
			<NcFormGroup :label="t('news', 'Articles (JSON)')">
				<div class="button-container">
					<NcButton
						aria-label="UploadArticles"
						:disabled="loading"
						@click="$refs.articlesFileSelect.click()">
						<template #icon>
							<UploadIcon :size="20" />
						</template>
					</NcButton>
					<input
						ref="articlesFileSelect"
						type="file"
						class="hidden"
						aria-hidden="true"
						accept=".json"
						@change="importArticles">
					<NcButton
						aria-label="DownloadArticles"
						:disabled="loading"
						@click="exportArticles">
						<template #icon>
							<DownloadIcon :size="20" />
						</template>
					</NcButton>
				</div>
				<NcNoteCard v-if="uploadArticlesStatusMessage" :type="uploadArticlesStatusType" :text="uploadArticlesStatusMessage" />
			</NcFormGroup>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="settings-keyboard" :name="t('news', 'Keyboard shortcuts')">
			<NcHotkeyList :label="t('news', 'General')">
				<NcHotkey :label="t('news', 'Refresh')" hotkey="R" />
			</NcHotkeyList>
			<NcHotkeyList :label="t('news', 'Screenreader mode only')">
				<NcHotkey :label="t('news', 'Jump to previous article')" hotkey="PageUp" />
				<NcHotkey :label="t('news', 'Jump to next article')" hotkey="PageDown" />
			</NcHotkeyList>
			<NcHotkeyList :label="t('news', 'Item navigation and control')">
				<NcHotkey :label="t('news', 'Jump to previous article')">
					<template #hotkey>
						<div>
							<NcKbd symbol="P" />
							/
							<NcKbd symbol="K" />
							/
							<NcKbd symbol="ArrowLeft" />
						</div>
					</template>
				</NcHotkey>
				<NcHotkey :label="t('news', 'Jump to next article')">
					<template #hotkey>
						<div>
							<NcKbd symbol="N" />
							/
							<NcKbd symbol="J" />
							/
							<NcKbd symbol="ArrowRight" />
						</div>
					</template>
				</NcHotkey>
				<NcHotkey :label="t('news', 'Open article in new tab')" hotkey="O" />
				<NcHotkey :label="t('news', 'Show article details in compact view')">
					<template #hotkey>
						<div>
							<NcKbd symbol="E" />
							/
							<NcKbd symbol="Enter" />
						</div>
					</template>
				</NcHotkey>
				<NcHotkey :label="t('news', 'Close article details in compact view')" hotkey="Escape" />
				<NcHotkey :label="t('news', 'Toggle star article')">
					<template #hotkey>
						<div>
							<NcKbd symbol="S" />
							/
							<NcKbd symbol="L" />
						</div>
					</template>
				</NcHotkey>
				<NcHotkey :label="t('news', 'Toggle keep current article unread')" hotkey="U" />
			</NcHotkeyList>
			<NcHotkeyList :label="t('news', 'Feed/Folder navigation and control')">
				<NcHotkey :label="t('news', 'Jump to previous feed')" hotkey="D" />
				<NcHotkey :label="t('news', 'Jump to next feed')" hotkey="F" />
				<NcHotkey :label="t('news', 'Jump to previous folder')" hotkey="C" />
				<NcHotkey :label="t('news', 'Jump to next folder')" hotkey="V" />
				<NcHotkey :label="t('news', 'Mark current articles feed/folder as read')" hotkey="Shift A" />
			</NcHotkeyList>
		</NcAppSettingsSection>

		<NcAppSettingsSection id="settings-resources" :name="t('news', 'Resources')">
			<NcFormBox>
				<NcFormBoxButton
					label="Documentation"
					href="https://nextcloud.github.io/news/"
					target="_blank" />
				<NcFormBoxButton
					label="GitHub Discussions"
					href="https://github.com/nextcloud/news/discussions"
					target="_blank" />
				<NcFormBoxButton
					label="GitHub Issues"
					href="https://github.com/nextcloud/news/issues"
					target="_blank" />
				<NcFormBoxButton
					label="Changelog"
					href="https://github.com/nextcloud/news/blob/master/CHANGELOG.md"
					target="_blank" />
			</NcFormBox>
		</NcAppSettingsSection>
	</NcAppSettingsDialog>
</template>

<script lang="ts">

import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'
import { defineComponent } from 'vue'
import NcAppSettingsDialog from '@nextcloud/vue/components/NcAppSettingsDialog'
import NcAppSettingsSection from '@nextcloud/vue/components/NcAppSettingsSection'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcFormBox from '@nextcloud/vue/components/NcFormBox'
import NcFormBoxButton from '@nextcloud/vue/components/NcFormBoxButton'
import NcFormBoxSwitch from '@nextcloud/vue/components/NcFormBoxSwitch'
import NcFormGroup from '@nextcloud/vue/components/NcFormGroup'
import NcHotkey from '@nextcloud/vue/components/NcHotkey'
import NcHotkeyList from '@nextcloud/vue/components/NcHotkeyList'
import NcKbd from '@nextcloud/vue/components/NcKbd'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcRadioGroup from '@nextcloud/vue/components/NcRadioGroup'
import NcRadioGroupButton from '@nextcloud/vue/components/NcRadioGroupButton'
import DownloadIcon from 'vue-material-design-icons/Download.vue'
import UploadIcon from 'vue-material-design-icons/Upload.vue'
import { DISPLAY_MODE, SPLIT_MODE } from '../../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'

export default defineComponent({
	name: 'AppSettingsDialog',
	components: {
		NcAppSettingsDialog,
		NcAppSettingsSection,
		NcButton,
		NcFormBox,
		NcFormBoxButton,
		NcFormBoxSwitch,
		NcFormGroup,
		NcHotkey,
		NcHotkeyList,
		NcKbd,
		NcLoadingIcon,
		NcNoteCard,
		NcRadioGroup,
		NcRadioGroupButton,
		DownloadIcon,
		UploadIcon,
	},

	emits: {
		close: () => true,
	},

	data: () => {
		return {
			DISPLAY_MODE,
			showSettings: false,
			displayModeOptions: [
				{
					id: DISPLAY_MODE.DEFAULT,
					name: t('news', 'Default'),
				},
				{
					id: DISPLAY_MODE.COMPACT,
					name: t('news', 'Compact'),
				},
				{
					id: DISPLAY_MODE.SCREENREADER,
					name: t('news', 'Screenreader'),
				},
			],

			splitModeOptions: [
				{
					id: SPLIT_MODE.VERTICAL,
					name: t('news', 'Vertical'),
				},
				{
					id: SPLIT_MODE.HORIZONTAL,
					name: t('news', 'Horizontal'),
				},
				{
					id: SPLIT_MODE.OFF,
					name: t('news', 'Off'),
				},
			],
		}
	},

	computed: {
		loading() {
			return this.$store.getters.loading
		},

		displaymode: {
			get() {
				return this.$store.getters.displaymode
			},

			set(newValue) {
				this.saveSetting('displaymode', newValue)
			},
		},

		splitmode: {
			get() {
				return this.$store.getters.splitmode
			},

			set(newValue) {
				if (this.displaymode !== DISPLAY_MODE.SCREENREADER) {
					this.saveSetting('splitmode', newValue)
				}
			},
		},

		preventReadOnScroll: {
			get() {
				return this.$store.getters.preventReadOnScroll
			},

			set(newValue) {
				this.saveSetting('preventReadOnScroll', newValue)
			},
		},

		showAll: {
			get() {
				return this.$store.getters.showAll
			},

			set(newValue) {
				this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
				this.saveSetting('showAll', newValue)
			},
		},

		oldestFirst: {
			get() {
				return this.$store.getters.oldestFirst
			},

			set(newValue) {
				this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
				this.saveSetting('oldestFirst', newValue)
			},
		},

		disableRefresh: {
			get() {
				return this.$store.getters.disableRefresh
			},

			set(newValue) {
				this.saveSetting('disableRefresh', newValue)
			},
		},

		uploadOpmlStatusMessage() {
			return this.$store.getters.lastOpmlImportMessage?.message
		},

		uploadOpmlStatusType() {
			return this.$store.getters.lastOpmlImportMessage?.type
		},

		uploadArticlesStatusMessage() {
			return this.$store.getters.lastArticlesImportMessage?.message
		},

		uploadArticlesStatusType() {
			return this.$store.getters.lastArticlesImportMessage?.type
		},
	},

	mounted() {
		this.showSettings = true
	},

	beforeUnmount() {
		this.showSettings = false
	},

	methods: {
		async saveSetting(key, value) {
			this.$store.commit(key, { value })
			const url = generateOcsUrl(
				'/apps/provisioning_api/api/v1/config/users/{appId}/{key}',
				{
					appId: 'news',
					key,
				},
			)
			if (typeof value === 'boolean') {
				value = value ? '1' : '0'
			}
			try {
				const { data } = await axios.post(url, {
					configValue: value,
				})
				this.handleResponse({
					status: data.ocs?.meta?.status,
				})
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Unable to update news config'),
					error: e,
				})
			}
		},

		async importOpml(event) {
			let result
			const file = event.target.files[0]
			if (!file || !file.name.endsWith('.opml')) {
				result = { type: 'error', message: t('news', 'Please select a valid OPML file') }
				this.$store.commit(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value: result })
				return
			}

			this.$store.commit(MUTATIONS.SET_LOADING, { value: true })
			const formData = new FormData()
			formData.append('file', file)

			try {
				const response = await axios.post(
					generateUrl('/apps/news/import/opml'),
					formData,
					{ headers: {} },
				)

				if (response.status === 200) {
					const data = response.data
					if (data.status === 'ok') {
						result = { type: 'success', message: t('news', 'File successfully uploaded') }
					} else {
						result = { type: 'warning', message: data.message }
					}
				} else {
					result = { type: 'error', message: t('news', 'Error uploading the opml file') }
				}
				this.$store.commit(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value: result })
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
					error: e,
				})
			}
			// refresh feeds and folders after import
			this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
			this.$store.dispatch(ACTIONS.FETCH_FEEDS)
			this.$store.commit(MUTATIONS.SET_LOADING, { value: false })
		},

		async exportOpml() {
			let result
			try {
				const response = await axios.get(
					generateUrl('/apps/news/export/opml'),
					{ responseType: 'blob' },
				)

				if (response.status === 200) {
					const formattedDate = new Date().toISOString().split('T')[0]
					const link = document.createElement('a')
					link.href = URL.createObjectURL(response.data)
					link.download = 'subscriptions-' + formattedDate + '.opml'
					link.click()
				} else {
					result = { type: 'error', message: t('news', 'Error retrieving the opml file') }
					this.$store.commit(MUTATIONS.SET_OPML_IMPORT_MESSAGE, { value: result })
				}
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
					error: e,
				})
			}
		},

		async importArticles(event) {
			let result
			const file = event.target.files[0]
			if (!file || !file.name.endsWith('.json')) {
				result = { type: 'error', message: t('news', 'Please select a valid json file') }
				this.$store.commit(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value: result })
				return
			}

			const formData = new FormData()
			formData.append('file', file)

			try {
				const response = await axios.post(
					generateUrl('/apps/news/import/articles'),
					formData,
					{ headers: {} },
				)

				if (response.status === 200) {
					const data = await response.data
					if (data.status === 'ok') {
						result = { type: 'success', message: t('news', 'File successfully uploaded') }
					} else {
						result = { type: 'warning', message: data.message }
					}
				} else {
					result = { type: 'error', message: t('news', 'Error uploading the json file') }
				}
				this.$store.commit(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value: result })
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
					error: e,
				})
			}
			this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		},

		async exportArticles() {
			let result
			try {
				const response = await axios.get(
					generateUrl('/apps/news/export/articles'),
					{ responseType: 'blob' },
				)

				if (response.status === 200) {
					const formattedDate = new Date().toISOString().split('T')[0]
					const link = document.createElement('a')
					link.href = URL.createObjectURL(response.data)
					link.download = 'articles-' + formattedDate + '.json'
					link.click()
				} else {
					result = { type: 'error', message: t('news', 'Error retrieving the json file') }
					this.$store.commit(MUTATIONS.SET_ARTICLES_IMPORT_MESSAGE, { value: result })
				}
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
					error: e,
				})
			}
		},

		handleResponse({ status, errorMessage, error }) {
			if (status !== 'ok') {
				showError(errorMessage)
				console.error(errorMessage, error)
			} else {
				showSuccess(t('news', 'Successfully updated news configuration'))
			}
		},
	},
})

</script>

<style scoped>
.button-container {
	display: flex;
	width: 100%;
}

.button-container button {
	flex: 1;
}
</style>
