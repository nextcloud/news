<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcModal
		size="normal"
		labelId="feed-configuration-title"
		:closeOnClickOutside="true"
		@close="$emit('close')">
		<div class="feed-configuration">
			<header>
				<div>
					<span class="eyebrow">{{ t('news', 'Feed configuration') }}</span>
					<h2 id="feed-configuration-title">
						{{ feed.title }}
					</h2>
					<a :href="feed.location" target="_blank" rel="noopener noreferrer">
						{{ feed.url }}
					</a>
				</div>
			</header>

			<section class="settings-section">
				<NcTextField
					:modelValue="title"
					:label="t('news', 'Feed title')"
					@update:modelValue="title = $event" />
				<NcButton @click="$emit('move', feed)">
					<template #icon>
						<FolderMoveIcon />
					</template>
					{{ t('news', 'Move to folder') }}
				</NcButton>
			</section>

			<section class="settings-section">
				<h3>{{ t('news', 'Update and content options') }}</h3>
				<div class="setting-row">
					<div>
						<strong>{{ t('news', 'Scheduled feed updates') }}</strong>
						<p>{{ t('news', 'Automatically check this feed for new articles') }}</p>
					</div>
					<NcCheckboxRadioSwitch
						type="switch"
						:modelValue="!feed.preventUpdate"
						:aria-label="t('news', 'Scheduled feed updates')"
						@update:modelValue="setPreventUpdate(!$event)" />
				</div>
				<div class="setting-row">
					<div>
						<strong>{{ t('news', 'Keep read status on update') }}</strong>
						<p>{{ t('news', 'Do not mark modified articles as unread') }}</p>
					</div>
					<NcCheckboxRadioSwitch
						type="switch"
						:modelValue="feed.updateMode === FEED_UPDATE_MODE.SILENT"
						:aria-label="t('news', 'Keep read status on update')"
						@update:modelValue="setUpdateMode($event ? FEED_UPDATE_MODE.SILENT : FEED_UPDATE_MODE.NORMAL)" />
				</div>
				<div>
					<div class="setting-row">
						<div>
							<strong>{{ t('news', 'Scrape full web article') }}</strong>
							<p>{{ t('news', 'Use the web article instead of the feed summary') }}</p>
						</div>
						<NcCheckboxRadioSwitch
							type="switch"
							:modelValue="feed.fullTextEnabled"
							:aria-label="t('news', 'Scrape full web article')"
							@update:modelValue="setFullText($event)" />
					</div>
					<NcNoteCard v-if="feed.fullTextEnabled" type="info">
						{{ t('news', 'Note: Web scraping may be blocked by some providers.') }}
					</NcNoteCard>
				</div>
			</section>

			<section class="settings-section">
				<h3>{{ t('news', 'Reading behavior') }}</h3>
				<NcSelect
					:modelValue="getUnreadAgeOption(feed)"
					:options="unreadAgeOptions"
					trackBy="value"
					label="label"
					:clearable="false"
					:searchable="false"
					:inputLabel="t('news', 'Automatically mark older articles as read')"
					@update:modelValue="setUnreadAgeOption($event)" />
				<NcTextField
					v-if="getUnreadAgeOption(feed).value === -1"
					:modelValue="String(feed.maxUnreadAge || 1)"
					type="number"
					min="1"
					step="1"
					:label="t('news', 'Custom number of days')"
					@update:modelValue="setCustomUnreadAge($event)" />
				<p class="helper">
					{{ t('news', 'Changes are applied to new and existing articles during the next feed update.') }}
				</p>
			</section>

			<section class="settings-section">
				<h3>{{ t('news', 'Keyword filters') }}</h3>
				<p class="helper">
					{{ t('news', 'Matching is case-insensitive. Title and body keywords match whole words, while URL keywords match URL fragments.') }}
				</p>
				<NcNoteCard v-if="filterError" type="error">
					{{ filterError }}
				</NcNoteCard>
				<NcTextField
					v-model:modelValue="filterForm.titleKeywords"
					:disabled="!filterLoaded"
					:label="t('news', 'Title keywords')"
					:placeholder="t('news', 'e.g. android, ios')" />
				<NcTextField
					v-model:modelValue="filterForm.bodyKeywords"
					:disabled="!filterLoaded"
					:label="t('news', 'Body keywords')"
					:placeholder="t('news', 'e.g. advertisement')" />
				<NcTextField
					v-model:modelValue="filterForm.urlKeywords"
					:disabled="!filterLoaded"
					:label="t('news', 'URL keywords')"
					:placeholder="t('news', 'e.g. /sport/')" />
				<div class="section-actions">
					<NcButton :disabled="filterSaving || !filterLoaded" @click="clearFilter">
						{{ t('news', 'Clear') }}
					</NcButton>
				</div>
			</section>

			<section class="diagnostics">
				<div>
					<span>{{ t('news', 'Last update') }}</span>
					<strong>{{ formatDate(feed.lastModified / 1000000) }}</strong>
				</div>
				<div>
					<span>{{ t('news', 'Next update') }}</span>
					<strong>{{ feed.nextUpdateTime && !feed.preventUpdate ? formatDate(feed.nextUpdateTime) : t('news', 'Not available') }}</strong>
				</div>
				<div>
					<span>{{ t('news', 'Consecutive errors') }}</span>
					<strong :class="{ error: feed.updateErrorCount > 0 }">{{ feed.updateErrorCount }}</strong>
				</div>
			</section>

			<footer>
				<NcButton variant="error" @click="deleteFeed">
					{{ t('news', 'Delete') }}
				</NcButton>
				<div>
					<NcButton @click="$emit('close')">
						{{ t('news', 'Cancel') }}
					</NcButton>
					<NcButton variant="primary" :disabled="!title.trim()" @click="saveAndClose">
						{{ t('news', 'Save changes') }}
					</NcButton>
				</div>
			</footer>
		</div>
	</NcModal>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'

import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcModal from '@nextcloud/vue/components/NcModal'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import FolderMoveIcon from 'vue-material-design-icons/FolderMove.vue'
import { FEED_UPDATE_MODE } from '../../enums/index.ts'
import { ACTIONS } from '../../store/index.ts'
import { formatDate } from '../../utils/dateUtils.ts'

export default defineComponent({
	components: {
		NcButton,
		NcCheckboxRadioSwitch,
		NcModal,
		NcNoteCard,
		NcSelect,
		NcTextField,
		FolderMoveIcon,
	},

	props: {
		feed: {
			type: Object,
			required: true,
		},

	},

	emits: {
		close: () => true,
		move: (feed: Feed) => Boolean(feed),
	},

	data() {
		const maxUnreadAge = (this.feed as Feed).maxUnreadAge || 0
		return {
			title: (this.feed as Feed).title || '',
			customUnreadAgeSelected: maxUnreadAge > 0 && ![1, 3, 7, 14, 30].includes(maxUnreadAge),
			filterSaving: false,
			filterLoaded: false,
			filterError: undefined as string | undefined,
			filterForm: {
				titleKeywords: '',
				bodyKeywords: '',
				urlKeywords: '',
			},

			FEED_UPDATE_MODE,
		}
	},

	computed: {
		unreadAgeOptions() {
			return [
				{ value: 0, label: this.t('news', 'Disabled - current behavior') },
				{ value: 1, label: this.t('news', '1 day') },
				{ value: 3, label: this.t('news', '3 days') },
				{ value: 7, label: this.t('news', '7 days') },
				{ value: 14, label: this.t('news', '14 days') },
				{ value: 30, label: this.t('news', '30 days') },
				{ value: -1, label: this.t('news', 'Custom number of days') },
			]
		},
	},

	async mounted() {
		try {
			const response = await this.$store.dispatch(ACTIONS.FEED_GET_FILTER, { feed: this.feed })
			const filter = response?.data?.filter
			if (filter) {
				this.filterForm.titleKeywords = filter.titleKeywords || ''
				this.filterForm.bodyKeywords = filter.bodyKeywords || ''
				this.filterForm.urlKeywords = filter.urlKeywords || ''
			}
			this.filterLoaded = true
		} catch (error) {
			this.filterError = this.filterErrorMessage(error)
		}
	},

	methods: {
		formatDate,

		setPreventUpdate(preventUpdate: boolean) {
			return this.$store.dispatch(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed: this.feed, preventUpdate })
		},

		setUpdateMode(updateMode: FEED_UPDATE_MODE) {
			return this.$store.dispatch(ACTIONS.FEED_SET_UPDATE_MODE, { feed: this.feed, updateMode })
		},

		setFullText(fullTextEnabled: boolean) {
			return this.$store.dispatch(ACTIONS.FEED_SET_FULL_TEXT, { feed: this.feed, fullTextEnabled })
		},

		getUnreadAgeOption(feed: Feed) {
			if (this.customUnreadAgeSelected) {
				return this.unreadAgeOptions[this.unreadAgeOptions.length - 1]
			}
			const maxUnreadAge = feed.maxUnreadAge || 0
			return this.unreadAgeOptions.find(({ value }) => value === maxUnreadAge)
				|| this.unreadAgeOptions[this.unreadAgeOptions.length - 1]
		},

		setUnreadAgeOption(option: { value: number } | null) {
			if (!option) {
				return
			}
			const isPreset = option.value !== -1
			this.customUnreadAgeSelected = !isPreset
			const maxUnreadAge = isPreset
				? option.value
				: (this.feed as Feed).maxUnreadAge > 0 ? (this.feed as Feed).maxUnreadAge : 2
			return this.$store.dispatch(ACTIONS.FEED_SET_MAX_UNREAD_AGE, { feed: this.feed, maxUnreadAge })
		},

		setCustomUnreadAge(value: string) {
			const maxUnreadAge = Math.max(1, parseInt(value, 10) || 1)
			return this.$store.dispatch(ACTIONS.FEED_SET_MAX_UNREAD_AGE, { feed: this.feed, maxUnreadAge })
		},

		async saveFilter() {
			this.filterSaving = true
			this.filterError = undefined
			try {
				await this.$store.dispatch(ACTIONS.FEED_SAVE_FILTER, {
					feed: this.feed,
					...this.filterForm,
				})
				return true
			} catch (error) {
				this.filterError = this.filterErrorMessage(error)
				return false
			} finally {
				this.filterSaving = false
			}
		},

		clearFilter() {
			this.filterForm.titleKeywords = ''
			this.filterForm.bodyKeywords = ''
			this.filterForm.urlKeywords = ''
			this.filterError = undefined
		},

		filterErrorMessage(error: unknown) {
			const responseError = error as { response?: { data?: { message?: string } } }
			return responseError?.response?.data?.message
				|| this.t('news', 'Unable to update keyword filters. Please try again.')
		},

		async saveAndClose() {
			if (this.filterLoaded && !await this.saveFilter()) {
				return
			}
			const title = this.title.trim()
			if (title && title !== (this.feed as Feed).title) {
				await this.$store.dispatch(ACTIONS.FEED_SET_TITLE, { feed: this.feed, title })
			}
			this.$emit('close')
		},

		async deleteFeed() {
			if (!window.confirm(this.t('news', 'Are you sure you want to delete?'))) {
				return
			}
			await this.$store.dispatch(ACTIONS.FEED_DELETE, { feed: this.feed })
			this.$emit('close')
		},
	},
})
</script>

<style lang="scss" scoped>
.feed-configuration {
	padding: 24px;
	min-width: min(560px, calc(100vw - 48px));
	max-height: 85vh;
	overflow-y: auto;

	header {
		margin-bottom: 24px;
		padding-inline-end: 44px;
	}

	h2 {
		margin: 2px 0 4px;
		font-size: 20px;
	}

	a {
		display: block;
		overflow: hidden;
		color: var(--color-text-maxcontrast);
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.eyebrow,
	h3 {
		color: var(--color-text-maxcontrast);
		font-size: 12px;
		font-weight: 600;
		letter-spacing: .04em;
		text-transform: uppercase;
	}
}

.settings-section {
	display: flex;
	flex-direction: column;
	gap: 12px;
	margin-bottom: 24px;
	padding-bottom: 24px;
	border-bottom: 1px solid var(--color-border);

	h3 {
		margin: 0;
	}
}

.setting-row,
footer,
.diagnostics div,
.section-actions {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
}

.setting-row {
	min-height: 44px;

	p,
	.helper {
		margin: 2px 0 0;
		color: var(--color-text-maxcontrast);
		font-size: 13px;
	}
}

.helper {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 13px;
}

.section-actions {
	justify-content: flex-end;
}

.diagnostics {
	display: grid;
	gap: 8px;
	margin-bottom: 24px;
	padding: 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	background: var(--color-background-dark);
	font-size: 13px;

	.error {
		color: var(--color-error-text);
	}
}

footer {
	> div {
		display: flex;
		gap: 8px;
	}
}
</style>
