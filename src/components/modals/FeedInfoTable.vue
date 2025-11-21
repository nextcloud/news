<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<MoveFeed v-if="showMoveFeed" :feed="feedToMove" @close="closeMoveFeed()" />
	<NcModal
		size="large"
		close-on-click-outside="true"
		@close="$emit('close')">
		<div class="table-modal">
			<div class="modal-header">
				<h2>{{ t('news', 'Feed settings') }}</h2>
				<div v-if="loading" class="loading-message">
					<NcLoadingIcon size="36" />
					<h1>{{ t('news', 'Importing feeds') }}...{{ t('news', 'Please wait') }}</h1>
				</div>
			</div>
			<table>
				<tbody>
					<tr>
						<td>
							{{ t('news', 'Last update') }}:
						</td>
						<td>
							{{ t('news', 'Time when the feed was last downloaded') }}
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
						<th />
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
									:feed-id="feed.id"
									@open-move-dialog="openMoveFeed(feed)" />
							</NcActions>
						</td>
						<td class="text">
							{{ feed.title }}
						</td>
						<td class="text">
							{{ folderName(feed) }}
						</td>
						<td class="date">
							{{ feed.preventUpdate ? t('news', 'Sync disabled') : formatDate(feed.lastModified / 1000000) }}
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
import NcActions from '@nextcloud/vue/components/NcActions'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import SortAscIcon from 'vue-material-design-icons/SortAscending.vue'
import SortDescIcon from 'vue-material-design-icons/SortDescending.vue'
import MoveFeed from '../MoveFeed.vue'
import SidebarFeedLinkActions from '../SidebarFeedLinkActions.vue'
import { formatDate } from '../../utils/dateUtils.ts'

export default {
	name: 'FeedInfoTable',
	components: {
		NcActions,
		NcLoadingIcon,
		NcModal,
		MoveFeed,
		SidebarFeedLinkActions,
		SortAscIcon,
		SortDescIcon,
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
			const sorted = [...this.feeds]
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
