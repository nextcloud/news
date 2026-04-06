<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<MoveFeed v-if="showMoveFeed" :feed="feedToMove" @close="closeMoveFeed()" />
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
							{{ t('news', 'Fetch options') }}
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
				<thead>
					<tr>
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
							<NcActions :inline="3" :data-test="'feedOptions-' + feed.id">
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
	</NcModal>
</template>

<script>
import { mapState } from 'vuex'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import FileDocumentCheck from 'vue-material-design-icons/FileDocumentCheck.vue'
import FileDocumentRefresh from 'vue-material-design-icons/FileDocumentRefresh.vue'
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
		NcModal,
		NcNoteCard,
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
	},

	emits: {
		close: () => true,
	},

	data() {
		return {
			feedToMove: undefined,
			showMoveFeed: false,
			sortKey: 'title',
			sortOrder: 1,
			FEED_UPDATE_MODE,
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

	methods: {
		formatDate,
		folderName(feed) {
			return this.folderMap[feed.folderId] || ''
		},

		openMoveFeed(feed) {
			this.feedToMove = feed
			this.showMoveFeed = true
		},

		closeMoveFeed() {
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

	}

	/* overwrite the fixed large modal width */
	:deep(.modal-wrapper--large > .modal-container) {
		max-width: 90%;
		width: max-content;
		max-height: min(90%, 100% - 2 * var(--header-height));
	}
</style>
