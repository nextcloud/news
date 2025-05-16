<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcModal
		size="large"
		@close="$emit('close')">
		<div class="table-modal">
			<div class="modal-header">
				<h2>{{ t('news', 'Article feed information') }}</h2>
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
								ID
								<div class="sort-icon">
									<SortAscIcon v-show="sortKey === 'id' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'id' && sortOrder !== 1" :size="20" />
								</div>
							</span>
						</th>
						<th @click="sortBy('title')">
							<span class="column-title">
								{{ t('news', 'Title') }}
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'title' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'title' && sortOrder !== 1" :size="20" />
								</span>
							</span>
						</th>
						<th @click="sortBy('lastModified')">
							<span class="column-title">
								{{ t('news', 'Last update') }}
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'lastModified' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'lastModified' && sortOrder !== 1" :size="20" />
								</span>
							</span>
						</th>
						<th @click="sortBy('nextUpdateTime')">
							<span class="column-title">
								{{ t('news', 'Next update') }}
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'nextUpdateTime' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'nextUpdateTime' && sortOrder !== 1" :size="20" />
								</span>
							</span>
						</th>
						<th
							:title="t('news', 'Articles per update')"
							@click="sortBy('articlesPerUpdate')">
							<span class="column-title">
								APU
								<span class="sort-icon">
									<SortAscIcon v-show="sortKey === 'articlesPerUpdate' && sortOrder === 1" :size="20" />
									<SortDescIcon v-show="sortKey === 'articlesPerUpdate' && sortOrder !== 1" :size="20" />
								</span>
							</span>
						</th>
						<th
							:title="t('news', 'Error Count') "
							@click="sortBy('updateErrorCount')">
							<span class="column-title">
								EC
								<span class="sort-icon">
									<SortAscIcon v-if="sortKey === 'updateErrorCount' && sortOrder === 1" :size="20" />
									<SortDescIcon v-if="sortKey === 'updateErrorCount' && sortOrder !== 1" :size="20" />
								</span>
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="feed in sortedFeeds" :key="feed.id">
						<td class="number">
							{{ feed.id }}
						</td>
						<td class="text">
							{{ feed.title }}
						</td>
						<td class="date">
							{{ formatDate(feed.lastModified / 1000) }}
						</td>
						<td class="date">
							{{ formatDate(feed.nextUpdateTime * 1000) }}
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
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import SortAscIcon from 'vue-material-design-icons/SortAscending.vue'
import SortDescIcon from 'vue-material-design-icons/SortDescending.vue'

export default {
	name: 'FeedInfoTable',
	components: {
		NcLoadingIcon,
		NcModal,
		SortAscIcon,
		SortDescIcon,
	},

	emits: {
		close: () => true,
	},

	data() {
		return {
			sortKey: 'title',
			sortOrder: 1,
		}
	},

	computed: {
		...mapState({
			feeds: (state) => state.feeds.feeds,
		}),

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
		formatDate(timestamp) {
			if (!timestamp) {
				return t('news', 'Not available')
			}
			return new Date(timestamp).toLocaleDateString(undefined, {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric',
				hour: '2-digit',
				minute: '2-digit',
				second: '2-digit',
			})
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
				text-align: left;
			}

			&.number {
				text-align: right;
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
