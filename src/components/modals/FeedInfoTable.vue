<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<MoveFeed
		v-if="showMoveFeed"
		:feeds="feedsToMove"
		@close="closeMoveFeed" />
	<FeedSettingsDialog
		v-if="configuredFeed"
		:feed="configuredFeed"
		@move="openMoveFeed"
		@close="closeFeedConfiguration" />
	<NcModal
		size="large"
		labelId="feed-settings-dialog"
		:closeOnClickOutside="true"
		@close="$emit('close')">
		<div class="table-modal">
			<header class="modal-header">
				<div class="modal-heading">
					<div>
						<h2 id="feed-settings-dialog">
							{{ t('news', 'Feed settings') }}
						</h2>
						<p>{{ t('news', 'Manage updates, reading behavior, and filters for your subscriptions.') }}</p>
					</div>
					<span class="feed-count">
						{{ feeds.length === 1 ? t('news', '1 feed') : t('news', '{count} feeds', { count: feeds.length }) }}
					</span>
				</div>
			</header>

			<NcNoteCard v-if="loading" type="info" data-test="loadingMessage">
				<div class="loading-message">
					<NcLoadingIcon :size="24" />
					<strong>{{ t('news', 'Loading feeds') }}... {{ t('news', 'Please wait') }}</strong>
				</div>
			</NcNoteCard>

			<section v-if="hasSelectedFeeds" class="bulk-toolbar" data-test="bulkToolbar">
				<div class="selection-summary">
					<NcButton
						variant="tertiary"
						:aria-label="t('news', 'Clear selection')"
						@click="selectedFeedIds = []">
						<template #icon>
							<CloseIcon />
						</template>
					</NcButton>
					<strong>{{ t('news', '{count} selected', { count: selectedFeedIds.length }) }}</strong>
				</div>
				<div class="bulk-actions">
					<NcButton :disabled="processingBatch" data-test="enableSelectedFeeds" @click="bulkSetPreventUpdate(false)">
						{{ t('news', 'Enable') }}
					</NcButton>
					<NcButton :disabled="processingBatch" data-test="disableSelectedFeeds" @click="bulkSetPreventUpdate(true)">
						{{ t('news', 'Disable') }}
					</NcButton>
					<NcButton :disabled="!canMoveSelected" data-test="moveSelectedFeeds" @click="openMoveSelectedFeedsDialog">
						{{ t('news', 'Move to folder') }}
					</NcButton>
					<NcActions
						:menuName="t('news', 'Mark read policy')"
						:disabled="processingBatch"
						:forceMenu="true"
						data-test="bulkReadPolicy">
						<NcActionButton :closeAfterClick="true" @click="bulkSetUpdateMode(FEED_UPDATE_MODE.SILENT)">
							{{ t('news', 'Keep read status on update') }}
						</NcActionButton>
						<NcActionButton :closeAfterClick="true" @click="bulkSetUpdateMode(FEED_UPDATE_MODE.NORMAL)">
							{{ t('news', 'Mark as unread on update') }}
						</NcActionButton>
					</NcActions>
					<NcButton
						variant="error"
						:disabled="!canDeleteSelected"
						data-test="deleteSelectedFeeds"
						@click="deleteSelectedFeeds">
						{{ t('news', 'Delete') }}
					</NcButton>
				</div>
			</section>

			<section v-else class="filter-toolbar">
				<NcTextField
					v-model:modelValue="searchQuery"
					class="feed-search"
					:label="t('news', 'Search feeds')"
					:placeholder="t('news', 'Search by title or URL')" />
				<NcSelect
					v-model="folderFilter"
					class="folder-filter"
					:options="folderOptions"
					trackBy="value"
					label="label"
					:clearable="false"
					:searchable="false"
					:inputLabel="t('news', 'Filter by folder')" />
				<NcCheckboxRadioSwitch
					type="switch"
					:modelValue="errorsOnly"
					:disabled="errorFeedCount === 0"
					:aria-label="t('news', 'Filter feeds with errors')"
					data-test="errorsOnly"
					class="errors-filter"
					@update:modelValue="errorsOnly = $event">
					<span class="errors-filter__label">
										{{ t('news', 'Feeds with errors') }}
										<NcCounterBubble v-if="errorFeedCount > 0" :count="errorFeedCount" />
					</span>
				</NcCheckboxRadioSwitch>
			</section>

			<div class="table-container">
				<table class="feeds-table">
					<thead>
						<tr>
							<th class="select-column">
								<NcCheckboxRadioSwitch
									:modelValue="allSelected"
									:indeterminate="isPartiallySelected"
									:aria-label="t('news', 'Select all visible feeds')"
									:disabled="processingBatch || sortedFeeds.length === 0"
									data-test="selectAllFeeds"
									class="table-select-checkbox"
									@update:modelValue="toggleSelectAllByValue" />
							</th>
							<th>
								<button type="button" class="sort-button" @click="sortBy('title')">
									{{ t('news', 'Title') }}
									<SortAscIcon v-if="sortKey === 'title' && sortOrder === 1" :size="18" />
									<SortDescIcon v-else-if="sortKey === 'title'" :size="18" />
								</button>
							</th>
							<th class="folder-column">
								<button type="button" class="sort-button" @click="sortBy('folderId')">
									{{ t('news', 'Folder') }}
									<SortAscIcon v-if="sortKey === 'folderId' && sortOrder === 1" :size="18" />
									<SortDescIcon v-else-if="sortKey === 'folderId'" :size="18" />
								</button>
							</th>
							<th class="update-column">
								<span :title="t('news', 'Time when the feed was last downloaded or modified. Hover over a date for the next update and article limit.')">
									{{ t('news', 'Last update') }}
								</span>
							</th>
							<th class="errors-column">
								<span :title="t('news', 'Number of errors since the last successful feed update')">
									{{ t('news', 'Errors') }}
								</span>
							</th>
							<th class="quick-column">
								{{ t('news', 'Quick toggles') }}
							</th>
							<th class="configure-column">
								<span class="visually-hidden">{{ t('news', 'Actions') }}</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="feed in sortedFeeds"
							:key="feed.id"
							:class="{ selected: isFeedSelected(feed) }">
							<td class="select-column">
								<NcCheckboxRadioSwitch
									v-model="selectedFeedIds"
									:value="String(feed.id)"
									:disabled="processingBatch"
									:aria-label="t('news', 'Select feed {feed}', { feed: feed.title || String(feed.id) })"
									:data-test="'selectFeed-' + feed.id"
									class="table-select-checkbox" />
							</td>
							<td class="feed-title-cell">
								<button type="button" class="feed-title" @click="openFeedConfiguration(feed)">
									<span class="feed-initial">{{ (feed.title || '?').charAt(0).toUpperCase() }}</span>
									<span class="feed-title-text">
										<strong>{{ feed.title }}</strong>
										<small>{{ displayFeedUrl(feed) }}</small>
									</span>
								</button>
							</td>
							<td class="folder-column">
								<span v-if="folderName(feed)" class="folder-pill">
									<FolderIcon :size="16" />
									{{ folderName(feed) }}
								</span>
								<span v-else class="muted">{{ t('news', 'No folder') }}</span>
							</td>
							<td class="update-column">
								<div
									class="update-status"
									:title="updateDetails(feed)">
									<strong>{{ formatDate(feed.lastModified / 1000000) }}</strong>
									<small>{{ t('news', 'Max articles per update: {count}', { count: feed.articlesPerUpdate }) }}</small>
								</div>
							</td>
							<td class="errors-column" :title="feed.lastUpdateError || undefined">
								<span v-if="feed.updateErrorCount > 0" class="error-badge">
									<AlertCircleIcon :size="14" />
									{{ feed.updateErrorCount }}
								</span>
								<span v-else class="muted">0</span>
							</td>
							<td class="quick-column">
								<div class="quick-toggles">
									<NcButton
										variant="tertiary"
										:pressed="!feed.preventUpdate"
										:aria-label="updateToggleLabel(feed)"
										:title="updateToggleLabel(feed)"
										:class="{ 'is-active': !feed.preventUpdate }"
										data-test="toggleFeedUpdate"
										@click="setPreventUpdate(feed, !feed.preventUpdate)">
										<template #icon>
											<SyncIcon />
										</template>
									</NcButton>
									<NcButton
										variant="tertiary"
										:pressed="feed.updateMode === FEED_UPDATE_MODE.SILENT"
										:aria-label="readPolicyLabel(feed)"
										:title="readPolicyLabel(feed)"
										:class="{ 'is-active': feed.updateMode === FEED_UPDATE_MODE.SILENT }"
										data-test="toggleReadPolicy"
										@click="setUpdateMode(feed, feed.updateMode === FEED_UPDATE_MODE.SILENT ? FEED_UPDATE_MODE.NORMAL : FEED_UPDATE_MODE.SILENT)">
										<template #icon>
											<FileDocumentCheckIcon />
										</template>
									</NcButton>
									<NcButton
										variant="tertiary"
										:pressed="feed.fullTextEnabled"
										:aria-label="scrapingLabel(feed)"
										:title="scrapingLabel(feed)"
										:class="{ 'is-active': feed.fullTextEnabled }"
										data-test="toggleScraping"
										@click="setFullText(feed, !feed.fullTextEnabled)">
										<template #icon>
											<TextBoxSearchIcon />
										</template>
									</NcButton>
								</div>
							</td>
							<td class="configure-column">
								<NcButton @click="openFeedConfiguration(feed)">
									<template #icon>
										<TuneIcon />
									</template>
									{{ t('news', 'Configure') }}
								</NcButton>
							</td>
						</tr>
					</tbody>
				</table>

				<div v-if="sortedFeeds.length === 0 && !loading" class="empty-state">
					<MagnifyIcon :size="36" />
					<h3>{{ t('news', 'No matching feeds found') }}</h3>
					<p>{{ t('news', 'Try changing or clearing the current filters.') }}</p>
					<NcButton variant="primary" @click="resetFilters">
						{{ t('news', 'Reset filters') }}
					</NcButton>
				</div>
			</div>
		</div>
	</NcModal>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'

import { showError } from '@nextcloud/dialogs'
import { defineComponent } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import AlertCircleIcon from 'vue-material-design-icons/AlertCircle.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import FileDocumentCheckIcon from 'vue-material-design-icons/FileDocumentCheck.vue'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import MagnifyIcon from 'vue-material-design-icons/Magnify.vue'
import SortAscIcon from 'vue-material-design-icons/SortAscending.vue'
import SortDescIcon from 'vue-material-design-icons/SortDescending.vue'
import SyncIcon from 'vue-material-design-icons/Sync.vue'
import TextBoxSearchIcon from 'vue-material-design-icons/TextBoxSearch.vue'
import TuneIcon from 'vue-material-design-icons/Tune.vue'
import MoveFeed from '../MoveFeed.vue'
import FeedSettingsDialog from './FeedSettingsDialog.vue'
import { FEED_UPDATE_MODE } from '../../enums/index.ts'
import { ACTIONS } from '../../store/index.ts'
import { formatDate } from '../../utils/dateUtils.ts'

type FeedWithMetadata = Feed & {
	lastModified: number
	nextUpdateTime: number
	articlesPerUpdate: number
}

export default defineComponent({
	components: {
		NcActions,
		NcActionButton,
		NcButton,
		NcCheckboxRadioSwitch,
		NcCounterBubble,
		NcLoadingIcon,
		NcModal,
		NcNoteCard,
		NcSelect,
		NcTextField,
		AlertCircleIcon,
		CloseIcon,
		FileDocumentCheckIcon,
		FolderIcon,
		MagnifyIcon,
		SortAscIcon,
		SortDescIcon,
		SyncIcon,
		TextBoxSearchIcon,
		TuneIcon,
		MoveFeed,
		FeedSettingsDialog,
	},

	emits: {
		close: () => true,
	},

	data() {
		return {
			feedsToMove: [] as Feed[],
			showMoveFeed: false,
			configuredFeed: undefined as FeedWithMetadata | undefined,
			selectedFeedIds: [] as string[],
			processingBatch: false,
			batchRequestDelay: 150,
			searchQuery: '',
			folderFilter: null as null | { value: number, label: string },
			errorsOnly: false,
			sortKey: 'title',
			sortOrder: 1,
			FEED_UPDATE_MODE,
		}
	},

	computed: {
		feeds(): FeedWithMetadata[] {
			return this.$store.state.feeds.feeds
		},

		folders(): Array<{ id: number, name: string }> {
			return this.$store.state.folders.folders
		},

		loading(): boolean {
			return this.$store.getters.loading
		},

		folderMap(): Record<number, string> {
			return Object.fromEntries(this.folders.map((folder) => [folder.id, folder.name]))
		},

		folderOptions() {
			return [
				{ value: -1, label: this.t('news', 'All folders') },
				{ value: 0, label: this.t('news', 'No folder') },
				...this.folders.map((folder) => ({ value: folder.id, label: folder.name })),
			]
		},

		errorFeedCount(): number {
			return this.feeds.filter((feed) => feed.updateErrorCount > 0).length
		},

		filteredFeeds(): FeedWithMetadata[] {
			const query = this.searchQuery.trim().toLocaleLowerCase()
			const selectedFolderId = this.folderFilter?.value ?? -1
			return this.feeds.filter((feed) => {
				const matchesQuery = !query
					|| (feed.title || '').toLocaleLowerCase().includes(query)
					|| (feed.url || feed.location || '').toLocaleLowerCase().includes(query)
				const matchesFolder = selectedFolderId === -1 || (feed.folderId || 0) === selectedFolderId
				const matchesErrors = !this.errorsOnly || feed.updateErrorCount > 0
				return matchesQuery && matchesFolder && matchesErrors
			})
		},

		sortedFeeds(): FeedWithMetadata[] {
			return [...this.filteredFeeds].sort((a, b) => {
				const valueA = a[this.sortKey as keyof FeedWithMetadata]
				const valueB = b[this.sortKey as keyof FeedWithMetadata]
				if (typeof valueA === 'string' && typeof valueB === 'string') {
					return valueA.localeCompare(valueB) * this.sortOrder
				}
				return ((Number(valueA) || 0) - (Number(valueB) || 0)) * this.sortOrder
			})
		},

		allSelected(): boolean {
			return this.sortedFeeds.length > 0
				&& this.sortedFeeds.every((feed) => this.selectedFeedIds.includes(String(feed.id)))
		},

		isPartiallySelected(): boolean {
			return !this.allSelected && this.sortedFeeds.some((feed) => this.selectedFeedIds.includes(String(feed.id)))
		},

		selectedFeeds(): FeedWithMetadata[] {
			const selectedIds = new Set(this.selectedFeedIds)
			return this.feeds.filter((feed) => selectedIds.has(String(feed.id)))
		},

		hasSelectedFeeds(): boolean {
			return this.selectedFeedIds.length > 0
		},

		canDeleteSelected(): boolean {
			return !this.processingBatch && this.selectedFeedIds.length > 0
		},

		canMoveSelected(): boolean {
			return !this.processingBatch && this.selectedFeedIds.length > 0
		},
	},

	watch: {
		feeds: {
			handler() {
				const feedIds = new Set(this.feeds.map((feed) => String(feed.id)))
				this.selectedFeedIds = this.selectedFeedIds.filter((feedId) => feedIds.has(feedId))
			},

			deep: true,
		},
	},

	methods: {
		formatDate,

		folderName(feed: Feed): string {
			return feed.folderId ? this.folderMap[feed.folderId] || '' : ''
		},

		displayFeedUrl(feed: Feed): string {
			return (feed.url || feed.location || '').replace(/^https?:\/\//, '')
		},

		updateDetails(feed: FeedWithMetadata): string {
			const nextUpdate = feed.nextUpdateTime && !feed.preventUpdate
				? formatDate(feed.nextUpdateTime)
				: this.t('news', 'Not available')
			return `${this.t('news', 'Next update')}: ${nextUpdate}\n${this.t('news', 'Max articles per update')}: ${feed.articlesPerUpdate}`
		},

		updateToggleLabel(feed: Feed): string {
			return feed.preventUpdate
				? this.t('news', 'Feed updates disabled — click to enable')
				: this.t('news', 'Feed updates enabled — click to disable')
		},

		readPolicyLabel(feed: Feed): string {
			return feed.updateMode === FEED_UPDATE_MODE.SILENT
				? this.t('news', 'Keep read status on update — click to mark updated articles unread')
				: this.t('news', 'Mark updated articles unread — click to keep read status')
		},

		scrapingLabel(feed: Feed): string {
			return feed.fullTextEnabled
				? this.t('news', 'Web scraping enabled — click to disable')
				: this.t('news', 'Web scraping disabled — click to enable')
		},

		isFeedSelected(feed: Feed): boolean {
			return this.selectedFeedIds.includes(String(feed.id))
		},

		toggleSelectAllByValue(checked: boolean) {
			const visibleIds = this.sortedFeeds.map((feed) => String(feed.id))
			const selected = new Set(this.selectedFeedIds)
			visibleIds.forEach((feedId) => checked ? selected.add(feedId) : selected.delete(feedId))
			this.selectedFeedIds = [...selected]
		},

		sortBy(key: string) {
			if (this.sortKey === key) {
				this.sortOrder *= -1
			} else {
				this.sortKey = key
				this.sortOrder = 1
			}
		},

		resetFilters() {
			this.searchQuery = ''
			this.folderFilter = null
			this.errorsOnly = false
		},

		setPreventUpdate(feed: Feed, preventUpdate: boolean) {
			return this.$store.dispatch(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed, preventUpdate })
		},

		setUpdateMode(feed: Feed, updateMode: FEED_UPDATE_MODE) {
			return this.$store.dispatch(ACTIONS.FEED_SET_UPDATE_MODE, { feed, updateMode })
		},

		setFullText(feed: Feed, fullTextEnabled: boolean) {
			return this.$store.dispatch(ACTIONS.FEED_SET_FULL_TEXT, { feed, fullTextEnabled })
		},

		openFeedConfiguration(feed: FeedWithMetadata) {
			this.configuredFeed = feed
		},

		closeFeedConfiguration() {
			this.configuredFeed = undefined
		},

		openMoveFeed(feed: Feed) {
			this.closeFeedConfiguration()
			this.feedsToMove = [feed]
			this.showMoveFeed = true
		},

		openMoveSelectedFeedsDialog() {
			if (!this.canMoveSelected) {
				return
			}
			this.feedsToMove = [...this.selectedFeeds]
			this.showMoveFeed = true
		},

		closeMoveFeed() {
			this.feedsToMove = []
			this.showMoveFeed = false
		},

		async pauseBetweenBatchRequests() {
			await new Promise((resolve) => setTimeout(resolve, this.batchRequestDelay))
		},

		async runBatch(action: (feed: FeedWithMetadata) => Promise<unknown>, failureMessage: string) {
			if (this.processingBatch || this.selectedFeeds.length === 0) {
				return
			}
			const feeds = [...this.selectedFeeds]
			const successfulIds = new Set<string>()
			let failed = 0
			this.processingBatch = true
			try {
				for (const [index, feed] of feeds.entries()) {
					try {
						await action(feed)
						successfulIds.add(String(feed.id))
					} catch (error) {
						failed++
						console.error(`error updating selected feed ${feed.id}`, error)
					}
					if (index < feeds.length - 1) {
						await this.pauseBetweenBatchRequests()
					}
				}
			} finally {
				this.processingBatch = false
				this.selectedFeedIds = this.selectedFeedIds.filter((feedId) => !successfulIds.has(feedId))
			}
			if (failed > 0) {
				showError(this.t('news', failureMessage))
			}
		},

		bulkSetPreventUpdate(preventUpdate: boolean) {
			return this.runBatch(
				(feed) => this.setPreventUpdate(feed, preventUpdate),
				'Some selected feeds could not be updated. Please try again later or check your connection.',
			)
		},

		bulkSetUpdateMode(updateMode: FEED_UPDATE_MODE) {
			return this.runBatch(
				(feed) => this.setUpdateMode(feed, updateMode),
				'The read policy could not be updated for some selected feeds. Please try again later.',
			)
		},

		async deleteSelectedFeeds() {
			if (!this.canDeleteSelected) {
				return
			}
			const count = this.selectedFeeds.length
			const shouldDelete = window.confirm(this.t('news', 'Are you sure you want to delete {count} selected feeds?', { count }))
			if (!shouldDelete) {
				return
			}
			return this.runBatch(
				(feed) => this.$store.dispatch(ACTIONS.FEED_DELETE, { feed }),
				'Some selected feeds could not be deleted. Please try again later or check your connection.',
			)
		},
	},
})
</script>

<style lang="scss" scoped>
.table-modal {
	display: flex;
	flex-direction: column;
	width: 100%;
	height: 90vh;
	padding: 24px;
	overflow: hidden;
}

.modal-header {
	display: flex;
	align-items: center;
	padding-inline-end: 48px;
	margin-bottom: 20px;
}

.modal-heading {
	display: flex;
	align-items: center;
	gap: 12px;

	h2 {
		margin: 0;
		font-weight: 600;
	}

	p {
		margin: 4px 0 0;
		color: var(--color-text-maxcontrast);
	}
}

.feed-count,
.folder-pill,
.error-badge {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	white-space: nowrap;
}

.feed-count {
	padding: 3px 10px;
	background: var(--color-background-dark);
}

.loading-message,
.filter-toolbar,
.bulk-toolbar,
.bulk-actions,
.selection-summary,
.quick-toggles {
	display: flex;
	align-items: center;
	gap: 8px;
}

.filter-toolbar,
.bulk-toolbar {
	justify-content: space-between;
	margin-bottom: 16px;
}

.filter-toolbar {
	.feed-search {
		flex: 1;
		max-width: 520px;
	}

	.folder-filter {
		width: 220px;
	}

	.errors-filter {
		flex-shrink: 0;
		min-height: 44px;

		:deep(.checkbox-radio-switch__content) {
			gap: 8px;
		}
		
		&__label {
				display: inline-flex;
				gap: 8px;
		}
	}
}

.bulk-toolbar {
	min-height: 52px;
	padding: 8px 12px;
	border: 1px solid var(--color-primary-element-light);
	border-radius: var(--border-radius-large);
	background: var(--color-primary-element-light);
}

.selection-summary {
	white-space: nowrap;
}

.bulk-actions {
	flex-wrap: wrap;
	justify-content: flex-end;
}

.table-container {
	flex: 1;
	min-height: 0;
	max-width: 100%;
	overflow: auto;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
}

.feeds-table {
	width: 100%;
	min-width: 980px;
	border-collapse: collapse;
	table-layout: fixed;

	thead {
		background: var(--color-background-dark);
	}

	th {
		height: 48px;
		padding: 8px 12px;
		color: var(--color-text-maxcontrast);
		font-size: 12px;
		font-weight: 600;
		text-align: start;
	}

	td {
		height: 68px;
		padding: 10px 12px;
		border-top: 1px solid var(--color-border);
		vertical-align: middle;
	}

	tbody tr {
		transition: background-color .15s ease;

		&:hover {
			background: var(--color-background-hover);
		}

		&.selected {
			background: var(--color-primary-element-light);
		}
	}

	.select-column {
		width: 44px;
		padding-inline: 12px 4px;
		text-align: center;
	}

	.folder-column {
		width: 170px;
	}

	.update-column {
		width: 170px;
	}

	.errors-column {
		width: 90px;
		text-align: center;
	}

	.quick-column {
		width: 190px;
	}

	.configure-column {
		width: 140px;
		text-align: end;
	}
}

.sort-button {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 0;
	border: 0;
	background: transparent;
	color: inherit;
	font: inherit;
}

.feed-title {
	display: flex;
	align-items: center;
	gap: 10px;
	width: 100%;
	min-width: 0;
	padding: 0;
	border: 0;
	background: transparent;
	color: var(--color-main-text);
	text-align: start;
}

.feed-initial {
	display: grid;
	flex: 0 0 34px;
	height: 34px;
	place-items: center;
	border: 1px solid var(--color-primary-element-light);
	border-radius: var(--border-radius-large);
	background: var(--color-primary-element-light);
	color: var(--color-primary-element);
	font-weight: 700;
}

.feed-title-text {
	display: flex;
	overflow: hidden;
	flex-direction: column;
	min-width: 0;

	strong,
	small {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	small {
		color: var(--color-text-maxcontrast);
	}
}

.folder-pill {
	max-width: 100%;
	padding: 4px 8px;
	background: var(--color-background-dark);
}

.update-status {
	display: flex;
	flex-direction: column;

	strong {
		font-size: 13px;
	}

	small {
		color: var(--color-text-maxcontrast);
	}
}

.error-badge {
	padding: 2px 8px;
	border-color: var(--color-error);
	background: var(--color-error-background);
	color: var(--color-error-text);
	font-weight: 600;
}

.quick-toggles {
	:deep(.button-vue) {
		border: 1px solid var(--color-border);
	}

	:deep(.button-vue.is-active) {
		border-color: var(--color-primary-element);
		background: var(--color-primary-element-light);
		color: var(--color-primary-element);
	}
}

.empty-state {
	display: flex;
	align-items: center;
	flex-direction: column;
	justify-content: center;
	gap: 8px;
	min-height: 260px;
	padding: 32px;
	color: var(--color-text-maxcontrast);
	text-align: center;

	h3 {
		margin: 4px 0 0;
		color: var(--color-main-text);
	}

	p {
		margin: 0 0 8px;
	}
}

.muted {
	color: var(--color-text-maxcontrast);
}

:deep(.table-select-checkbox.checkbox-radio-switch) {
	display: inline-flex;
	justify-content: center;
	max-width: unset;
	min-height: auto;
	padding: 0;
}

:deep(.modal-wrapper--large > .modal-container) {
	width: min(94vw, 1500px);
	max-width: 94vw;
	max-height: min(90vh, calc(100vh - 2 * var(--header-height)));
}

:deep(.modal-container__close) {
	top: 20px;
	inset-inline-end: 20px;
}

@media (max-width: 900px) {
	.feed-initial {
		display: none;
	}

	.filter-toolbar,
	.bulk-toolbar {
		align-items: stretch;
		flex-direction: column;
	}

	.filter-toolbar {
		.feed-search,
		.folder-filter {
			width: 100%;
			max-width: none;
		}
	}

	.bulk-actions {
		justify-content: flex-start;
	}
}
</style>
