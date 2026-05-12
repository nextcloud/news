<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<MoveFeed
		v-if="showMoveFeed"
		:feeds="feedsToMove"
		@close="closeMoveFeed()" />
	<NcModal
		size="large"
		labelId="feed-settings-dialog"
		:closeOnClickOutside="true"
		@close="$emit('close')">
		<div class="table-modal">
			<div class="modal-header">
				<h2 id="feed-settings-dialog">
					{{ t('news', 'Feed settings') }}
				</h2>
			</div>
			<table>
				<tbody>
					<tr>
						<td>
							{{ t('news', 'Last update') }}:
						</td>
						<td>
							{{ t('news', 'Time when the feed was last downloaded or modified') }}
						</td>
					</tr>
					<tr>
						<td>
							{{ t('news', 'Next update') }}:
						</td>
						<td>
							{{ t('news', 'Time when the next feed update will be done') }}<br>
							({{ t('news', 'Only if activated in the admin settings, otherwise the regular update interval is used') }})
						</td>
					</tr>
					<tr>
						<td>
							APU ({{ t('news', 'Articles per update') }}):
						</td>
						<td>
							{{ t('news', 'Maximum number of articles reached in a feed update') }}
						</td>
					</tr>
					<tr>
						<td>
							EC ({{ t('news', 'Error Count') }}):
						</td>
						<td>
							{{ t('news', 'Number of errors that have occurred since the last successful feed update') }}
						</td>
					</tr>
				</tbody>
			</table>
			<table>
				<tbody>
					<tr>
						<th colspan="4">
							{{ t('news', 'Feed fetch options') }}
						</th>
					</tr>
					<tr>
						<td>
							<Sync />
						</td>
						<td>
							{{ t('news', 'Feed update is enabled') }}
						</td>
						<td>
							<SyncOff />
						</td>
						<td>
							{{ t('news', 'Feed update is disabled') }}
						</td>
					</tr>
					<tr>
						<td>
							<FileDocumentCheck />
						</td>
						<td>
							{{ t('news', 'Keep read status on update') }}
						</td>
						<td>
							<FileDocumentRefresh />
						</td>
						<td>
							{{ t('news', 'Mark as unread on update') }}
						</td>
					</tr>
					<tr>
						<td>
							<TextShortIcon />
						</td>
						<td>
							{{ t('news', 'Use article text provided by the feed') }}
						</td>
						<td>
							<TextLongIcon />
						</td>
						<td>
							{{ t('news', 'Scrape web version of the article text') }}
						</td>
					</tr>
				</tbody>
			</table>
			<NcNoteCard type="info">
				{{ t('news', 'Please note that web scraping may be blocked by some providers and is generally discouraged.') }}
			</NcNoteCard>
			<NcNoteCard v-if="loading" type="info" data-test="loadingMessage">
				<div class="loading-message">
					<NcLoadingIcon size="24" />
					<strong>{{ t('news', 'Loading feeds') }}...{{ t('news', 'Please wait') }}</strong>
				</div>
			</NcNoteCard>
			<table>
				<tbody>
					<tr>
						<th colspan="4">
							{{ t('news', 'Keyword filters') }}
						</th>
					</tr>
					<tr>
						<td>
							<FilterIcon />
						</td>
						<td colspan="3">
							{{ t('news', 'Hide articles that match comma-separated keywords (case-insensitive) in the title, body, or URL') }}
						</td>
					</tr>
				</tbody>
			</table>
			<table ref="feedsTable" class="feeds-table" :style="feedsTableStyle">
				<colgroup>
					<col style="width: 36px;">
					<col style="width: 64px;">
					<col style="width: 44px;">
					<col style="width: 180px;">
					<col style="width: 260px;">
					<col style="width: 170px;">
					<col style="width: 160px;">
					<col style="width: 160px;">
					<col style="width: 70px;">
					<col style="width: 70px;">
				</colgroup>
				<thead>
					<tr v-if="hasSelectedFeeds" class="selection-header-row">
						<th class="select-column">
							<NcCheckboxRadioSwitch
								:modelValue="allSelected"
								:indeterminate="isPartiallySelected"
								data-test="selectAllFeeds"
								:aria-label="t('news', 'Select all feeds')"
								:disabled="processingBatch || sortedFeeds.length === 0"
								class="table-select-checkbox"
								@update:modelValue="toggleSelectAllByValue" />
						</th>
						<th colspan="9" class="selection-header-cell">
							<div class="selection-header-content">
								<strong>{{ t('news', 'Selected feeds') }}: {{ selectedFeedIds.length }}</strong>
								<NcButton
									:disabled="!canMoveSelected"
									data-test="moveSelectedFeeds"
									@click="openMoveSelectedFeedsDialog">
									{{ t('news', 'Move selected') }}
								</NcButton>
								<NcButton
									:disabled="!canDeleteSelected"
									data-test="deleteSelectedFeeds"
									@click="deleteSelectedFeeds">
									{{ t('news', 'Delete selected') }}
								</NcButton>
							</div>
						</th>
					</tr>
					<tr v-else>
						<th class="select-column">
							<NcCheckboxRadioSwitch
								:modelValue="allSelected"
								:indeterminate="isPartiallySelected"
								data-test="selectAllFeeds"
								:aria-label="t('news', 'Select all feeds')"
								:disabled="processingBatch || sortedFeeds.length === 0"
								class="table-select-checkbox"
								@update:modelValue="toggleSelectAllByValue" />
						</th>
						<th @click="sortBy('id')">
							<span class="column-title">
								<div class="sort-icon">
									<SortAscIcon v-show="sortKey === 'id' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'id' && sortOrder !== 1" :size="20" />
								</div>
								ID
							</span>
						</th>
						<th colspan="2">
							<span class="column-title">
								{{ t('news', 'Options') }}
							</span>
						</th>
						<th @click="sortBy('title')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'title' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'title' && sortOrder !== 1" :size="20" />
								</span>
								{{ t('news', 'Title') }}
							</span>
						</th>
						<th @click="sortBy('folderId')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'folderId' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'folderId' && sortOrder !== 1" :size="20" />
								</span>
								{{ t('news', 'Folder') }}
							</span>
						</th>
						<th @click="sortBy('lastModified')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'lastModified' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'lastModified' && sortOrder !== 1" :size="20" />
								</span>
								{{ t('news', 'Last update') }}
							</span>
						</th>
						<th @click="sortBy('nextUpdateTime')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'nextUpdateTime' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'nextUpdateTime' && sortOrder !== 1" :size="20" />
								</span>
								{{ t('news', 'Next update') }}
							</span>
						</th>
						<th
							:title="t('news', 'Articles per update')"
							@click="sortBy('articlesPerUpdate')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'articlesPerUpdate' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'articlesPerUpdate' && sortOrder !== 1" :size="20" />
								</span>
								APU
							</span>
						</th>
						<th
							:title="t('news', 'Error Count') "
							@click="sortBy('updateErrorCount')">
							<span class="column-title">
								<span class="sort-icon">
									<SortAscIcon v-if="sortKey === 'updateErrorCount' && sortOrder === 1" :size="20" />
									<SortDescIcon v-if="sortKey === 'updateErrorCount' && sortOrder !== 1" :size="20" />
								</span>
								EC
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="feed in sortedFeeds" :key="feed.id">
						<td class="select-column">
							<NcCheckboxRadioSwitch
								v-model="selectedFeedIds"
								:value="String(feed.id)"
								:disabled="processingBatch"
								:aria-label="t('news', 'Select feed {feed}', { feed: feed.title || String(feed.id) })"
								:data-test="'selectFeed-' + feed.id"
								class="table-select-checkbox" />
						</td>
						<td class="number">
							{{ feed.id }}
						</td>
						<td>
							<NcActions>
								<SidebarFeedLinkActions
									:feedId="feed.id"
									@openMoveDialog="openMoveFeed(feed)" />
							</NcActions>
						</td>
						<td>
							<NcActions :inline="4" :data-test="'feedOptions-' + feed.id">
								<NcActionButton
									v-if="feed.preventUpdate"
									:title="t('news', 'Enable feed update')"
									data-test="enableFeedUpdate"
									@click="setPreventUpdate(feed, false)">
									<template #icon>
										<SyncOff />
									</template>
								</NcActionButton>
								<NcActionButton
									v-if="!feed.preventUpdate"
									:title="t('news', 'Disable feed update')"
									data-test="disableFeedUpdate"
									@click="setPreventUpdate(feed, true)">
									<template #icon>
										<Sync />
									</template>
								</NcActionButton>
								<NcActionButton
									v-if="feed.updateMode === FEED_UPDATE_MODE.NORMAL"
									:title="t('news', 'Disable marking items as unread on update')"
									data-test="disableMarkUnread"
									@click="setUpdateMode(feed, FEED_UPDATE_MODE.SILENT)">
									<template #icon>
										<FileDocumentRefresh />
									</template>
								</NcActionButton>
								<NcActionButton
									v-if="feed.updateMode === FEED_UPDATE_MODE.SILENT"
									:title="t('news', 'Enable marking items as unread on update')"
									data-test="enableMarkUnread"
									@click="setUpdateMode(feed, FEED_UPDATE_MODE.NORMAL)">
									<template #icon>
										<FileDocumentCheck />
									</template>
								</NcActionButton>
								<NcActionButton
									v-if="!feed.fullTextEnabled"
									:title="t('news', 'Enable web scraping')"
									data-test="enableScraping"
									@click="setFullText(feed, true)">
									<template #icon>
										<TextShortIcon />
									</template>
								</NcActionButton>
								<NcActionButton
									v-if="feed.fullTextEnabled"
									:title="t('news', 'Disable web scraping')"
									data-test="disableScraping"
									@click="setFullText(feed, false)">
									<template #icon>
										<TextLongIcon />
									</template>
								</NcActionButton>
								<NcActionButton
									:title="t('news', 'Keyword filters')"
									@click="openFilterDialog(feed)">
									<template #icon>
										<FilterIcon />
									</template>
								</NcActionButton>
							</NcActions>
						</td>
						<td class="text">
							{{ feed.title }}
						</td>
						<td class="text">
							{{ folderName(feed) }}
						</td>
						<td class="date">
							{{ formatDate(feed.lastModified / 1000000) }}
						</td>
						<td class="date">
							{{ feed.nextUpdateTime && !feed.preventUpdate ? formatDate(feed.nextUpdateTime) : t('news', 'Not available') }}
						</td>
						<td class="number">
							{{ feed.articlesPerUpdate }}
						</td>
						<td class="number" :title="feed.lastUpdateError">
							{{ feed.updateErrorCount }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<NcModal
			v-if="filterFeed"
			labelId="feed-filter-dialog"
			size="small"
			@close="closeFilterDialog()">
			<div class="filter-dialog">
				<h3 id="feed-filter-dialog">
					{{ t('news', 'Keyword Filters for {feed}', { feed: filterFeed.title }) }}
				</h3>
				<p class="filter-help-text">
					{{ t('news', 'Matching is case-insensitive. Title and body keywords match whole words, while URL keywords match URL fragments.') }}
				</p>
				<NcNoteCard v-if="filterDialogError" type="error">
					{{ filterDialogError }}
				</NcNoteCard>

				<div class="filter-inputs">
					<NcTextField
						id="filter-title-keywords"
						v-model:modelValue="filterForm.titleKeywords"
						:label="t('news', 'Title keywords')"
						:placeholder="t('news', 'e.g. android, ios')" />
					<NcTextField
						id="filter-body-keywords"
						v-model:modelValue="filterForm.bodyKeywords"
						:label="t('news', 'Body keywords')"
						:placeholder="t('news', 'e.g. advertisement')" />

					<NcTextField
						id="filter-url-keywords"
						v-model:modelValue="filterForm.urlKeywords"
						:label="t('news', 'URL keywords')"
						:placeholder="t('news', 'e.g. /sport/')" />
				</div>

				<div class="filter-actions">
					<NcButton :disabled="filterDialogSaving" @click="saveFilter()">
						{{ t('news', 'Save') }}
					</NcButton>
					<NcButton :disabled="filterDialogSaving" @click="clearFilter()">
						{{ t('news', 'Clear') }}
					</NcButton>
				</div>
			</div>
		</NcModal>
	</NcModal>
</template>

<script>
import { showError } from '@nextcloud/dialogs'
import { mapState } from 'vuex'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import FileDocumentCheck from 'vue-material-design-icons/FileDocumentCheck.vue'
import FileDocumentRefresh from 'vue-material-design-icons/FileDocumentRefresh.vue'
import FilterIcon from 'vue-material-design-icons/Filter.vue'
import SortAscIcon from 'vue-material-design-icons/SortAscending.vue'
import SortDescIcon from 'vue-material-design-icons/SortDescending.vue'
import Sync from 'vue-material-design-icons/Sync.vue'
import SyncOff from 'vue-material-design-icons/SyncOff.vue'
import TextLongIcon from 'vue-material-design-icons/TextLong.vue'
import TextShortIcon from 'vue-material-design-icons/TextShort.vue'
import MoveFeed from '../MoveFeed.vue'
import SidebarFeedLinkActions from '../SidebarFeedLinkActions.vue'
import { FEED_UPDATE_MODE } from '../../enums/index.ts'
import { ACTIONS } from '../../store/index.ts'
import { formatDate } from '../../utils/dateUtils.ts'

export default {
	name: 'FeedInfoTable',
	components: {
		NcActions,
		NcActionButton,
		NcLoadingIcon,
		NcButton,
		NcCheckboxRadioSwitch,
		NcModal,
		NcNoteCard,
		NcTextField,
		MoveFeed,
		SidebarFeedLinkActions,
		FileDocumentRefresh,
		FileDocumentCheck,
		SortAscIcon,
		SortDescIcon,
		Sync,
		SyncOff,
		TextShortIcon,
		TextLongIcon,
		FilterIcon,
	},

	emits: {
		close: () => true,
	},

	data() {
		return {
			feedsToMove: [],
			showMoveFeed: false,
			selectedFeedIds: [],
			preservedTableWidth: null,
			processingBatch: false,
			batchRequestDelay: 150,
			sortKey: 'title',
			sortOrder: 1,
			FEED_UPDATE_MODE,
			filterFeed: undefined,
			filterDialogError: undefined,
			filterDialogSaving: false,
			filterForm: {
				titleKeywords: '',
				bodyKeywords: '',
				urlKeywords: '',
			},
		}
	},

	computed: {
		...mapState({
			feeds: (state) => state.feeds.feeds,
			folders: (state) => state.folders.folders,
		}),

		folderMap() {
			return this.folders.reduce((map, folder) => {
				map[folder.id] = folder.name
				return map
			}, {})
		},

		loading() {
			return this.$store.getters.loading
		},

		allSelected() {
			return this.sortedFeeds.length > 0 && this.selectedFeedIds.length === this.sortedFeeds.length
		},

		isPartiallySelected() {
			return this.selectedFeedIds.length > 0 && this.selectedFeedIds.length < this.sortedFeeds.length
		},

		canDeleteSelected() {
			return !this.processingBatch && this.selectedFeedIds.length > 0
		},

		canMoveSelected() {
			return !this.processingBatch && this.selectedFeedIds.length > 0
		},

		hasSelectedFeeds() {
			return this.selectedFeedIds.length > 0
		},

		feedsTableStyle() {
			if (!this.hasSelectedFeeds || !this.preservedTableWidth) {
				return undefined
			}

			return {
				minWidth: `${this.preservedTableWidth}px`,
			}
		},

		selectedFeeds() {
			const selectedFeedIdSet = new Set(this.selectedFeedIds)
			return this.feeds.filter((feed) => selectedFeedIdSet.has(String(feed.id)))
		},

		sortedFeeds() {
			const sorted = Array.isArray(this.feeds) ? [...this.feeds] : []
			if (this.sortKey) {
				sorted.sort((a, b) => {
					const valueA = a[this.sortKey]
					const valueB = b[this.sortKey]
					if (typeof valueA === 'string') {
						return valueA.localeCompare(valueB) * this.sortOrder
					} else {
						return (valueA - valueB) * this.sortOrder
					}
				})
			}
			return sorted
		},
	},

	watch: {
		feeds() {
			const feedIds = new Set(this.feeds.map((feed) => feed.id))
			this.selectedFeedIds = this.selectedFeedIds.filter((feedId) => feedIds.has(feedId))
			this.$nextTick(() => {
				if (!this.hasSelectedFeeds) {
					this.updatePreservedTableWidth()
				}
			})
		},

		hasSelectedFeeds(selected) {
			if (!selected) {
				this.$nextTick(() => {
					this.updatePreservedTableWidth()
				})
			}
		},
	},

	mounted() {
		this.$nextTick(() => {
			this.updatePreservedTableWidth()
		})
		window.addEventListener('resize', this.handleResize)
	},

	beforeUnmount() {
		window.removeEventListener('resize', this.handleResize)
	},

	methods: {
		formatDate,

		handleResize() {
			if (this.hasSelectedFeeds) {
				return
			}

			this.$nextTick(() => {
				this.updatePreservedTableWidth()
			})
		},

		updatePreservedTableWidth() {
			const table = this.$refs.feedsTable
			if (!table) {
				return
			}

			const width = table.getBoundingClientRect().width
			if (width > 0) {
				this.preservedTableWidth = Math.ceil(width)
			}
		},

		folderName(feed) {
			return this.folderMap[feed.folderId] || ''
		},

		async pauseBetweenBatchRequests() {
			await new Promise((resolve) => setTimeout(resolve, this.batchRequestDelay))
		},

		getFolderIdOrDefault(feed) {
			return typeof feed.folderId === 'number' ? feed.folderId : 0
		},

		toggleSelectAllByValue(checked) {
			if (checked) {
				this.selectedFeedIds = this.sortedFeeds.map((feed) => String(feed.id))
				return
			}
			this.selectedFeedIds = []
		},

		openMoveFeed(feed) {
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

		sortBy(key) {
			if (this.sortKey === key) {
				this.sortOrder *= -1
			} else {
				this.sortKey = key
				this.sortOrder = 1
			}
		},

		setPreventUpdate(feed, preventUpdate) {
			this.$store.dispatch(ACTIONS.FEED_SET_PREVENT_UPDATE, { feed, preventUpdate })
		},

		setUpdateMode(feed, updateMode) {
			this.$store.dispatch(ACTIONS.FEED_SET_UPDATE_MODE, { feed, updateMode })
		},

		setFullText(feed, fullTextEnabled) {
			this.$store.dispatch(ACTIONS.FEED_SET_FULL_TEXT, { feed, fullTextEnabled })
		},

		async openFilterDialog(feed) {
			this.filterFeed = feed
			this.filterDialogError = undefined
			try {
				const response = await this.$store.dispatch(ACTIONS.FEED_GET_FILTER, { feed })
				if (response?.data?.filter) {
					this.filterForm.titleKeywords = response.data.filter.titleKeywords || ''
					this.filterForm.bodyKeywords = response.data.filter.bodyKeywords || ''
					this.filterForm.urlKeywords = response.data.filter.urlKeywords || ''
				}
			} catch (error) {
				this.filterDialogError = this.filterErrorMessage(error)
			}
		},

		closeFilterDialog() {
			this.filterFeed = undefined
			this.filterDialogError = undefined
		},

		async saveFilter() {
			if (!this.filterFeed || this.filterDialogSaving) {
				return
			}

			this.filterDialogError = undefined
			this.filterDialogSaving = true
			try {
				await this.$store.dispatch(ACTIONS.FEED_SAVE_FILTER, {
					feed: this.filterFeed,
					titleKeywords: this.filterForm.titleKeywords,
					bodyKeywords: this.filterForm.bodyKeywords,
					urlKeywords: this.filterForm.urlKeywords,
				})
				this.closeFilterDialog()
			} catch (error) {
				this.filterDialogError = this.filterErrorMessage(error)
			} finally {
				this.filterDialogSaving = false
			}
		},

		async clearFilter() {
			if (!this.filterFeed || this.filterDialogSaving) {
				return
			}

			this.filterDialogError = undefined
			this.filterDialogSaving = true
			try {
				await this.$store.dispatch(ACTIONS.FEED_DELETE_FILTER, { feed: this.filterFeed })
				this.filterForm.titleKeywords = ''
				this.filterForm.bodyKeywords = ''
				this.filterForm.urlKeywords = ''
				this.closeFilterDialog()
			} catch (error) {
				this.filterDialogError = this.filterErrorMessage(error)
			} finally {
				this.filterDialogSaving = false
			}
		},

		filterErrorMessage(error) {
			return error?.response?.data?.message || t('news', 'Unable to update keyword filters. Please try again.')
		},

		async deleteSelectedFeeds() {
			if (!this.canDeleteSelected) {
				return
			}

			const selectedFeeds = [...this.selectedFeeds]
			const shouldDelete = window.confirm(t('news', 'Are you sure you want to delete {count} selected feeds?', { count: selectedFeeds.length }))
			if (!shouldDelete) {
				return
			}

			this.processingBatch = true
			let failedDeletes = 0

			try {
				for (const [index, feed] of selectedFeeds.entries()) {
					try {
						await this.$store.dispatch(ACTIONS.FEED_DELETE, { feed })
					} catch (error) {
						failedDeletes++
						console.error(`error deleting selected feed ${feed.id}`, error)
					}
					if (index < selectedFeeds.length - 1) {
						await this.pauseBetweenBatchRequests()
					}
				}
			} finally {
				this.processingBatch = false
			}

			if (failedDeletes > 0) {
				showError(t('news', 'Some selected feeds could not be deleted. Please try again later or check your connection.'))
			}
		},
	},
}
</script>

<style lang="scss" scoped>
	.table-modal {
		width: max-content;
		padding: 30px 40px 20px;

		h2 {
			font-weight: bold;
		}
	}

	.modal-header {
		display: flex;
		align-items: center;
		gap: 1rem;
	}

	.loading-message {
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}

	.selection-header-cell {
		cursor: default;

		&:hover {
			background-color: transparent;
			border-radius: 0;
		}
	}

	.selection-header-content {
		display: flex;
		align-items: center;
		justify-content: flex-start;
		gap: 0.75rem;
		padding: 0 1rem 0 0;
		min-height: 52px;
		white-space: nowrap;
	}

	.feeds-table {
		table-layout: fixed;

		thead tr {
			height: 52px;
		}

		th {
			height: 52px;
			vertical-align: middle;
		}

		.column-title {
			min-height: 52px;
		}

		:deep(.table-select-checkbox.checkbox-radio-switch) {
			min-height: auto;
			padding: 0;
			display: inline-flex;
			justify-content: center;
			max-width: unset;
		}
	}

	table {
		margin-top: 24px;
		border-collapse: collapse;

		tbody tr {
			&:hover, &:focus, &:active {
				background-color: transparent !important;
			}
		}

		thead tr {
			border: none;
		}

		th {
			cursor: pointer;
			font-weight: bold;
			border-bottom: 2px solid var(--color-background-darker);
			&:hover {
				background-color: var(--color-background-hover);
				border-radius: 10px;
			}
		}

		th * {
			cursor: pointer;
		}

		td {
			padding: .25rem 1rem .25rem 0;
			border-top: 1px solid var(--color-background-dark);
			border-bottom: unset;

			&.text {
				text-align: start;
			}

			&.number {
				text-align: end;
			}

			&.date {
				text-align: center;
			}
		}

		.column-title {
			width: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 4px;
			padding: .25rem 1rem .25rem 0;
		}

		.sort-icon {
			height: 20px;
			width: 20px;
		}

		.select-column {
			text-align: center;
			padding-inline-end: .5rem;
			min-width: 36px;
		}

	}

	/* overwrite the fixed large modal width */
	:deep(.modal-wrapper--large > .modal-container) {
		max-width: 90%;
		width: max-content;
		max-height: min(90%, 100% - 2 * var(--header-height));
	}

	.filter-dialog {
		padding: 20px;

		h3 {
			font-size: 1.2rem;
			margin-bottom: 16px;
		}

		.filter-help-text {
			margin: 0 0 12px;
			color: var(--color-text-maxcontrast);
			font-size: 0.95rem;
		}

		:deep(.notecard) {
			margin-bottom: 12px;
		}

		.filter-inputs {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		.filter-actions {
			display: flex;
			gap: 8px;
			justify-content: flex-end;
			margin-top: 16px;
		}
	}
</style>
