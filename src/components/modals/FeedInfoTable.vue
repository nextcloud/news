<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud News
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcModal size="large"
		@close="$emit('close')">
		<div class="table-modal">
			<h2>{{ t('news', 'Article feed information') }}</h2>
			<table>
				<thead>
					<tr>
						<th @click="sortBy('id')">
							<span class="column-title">
								ID
								<SortAscIcon v-if="sortKey === 'id' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'id' && sortOrder !== 1" />
							</span>
						</th>
						<th @click="sortBy('title')">
							<span class="column-title">
								{{ t('news', 'Title') }}
								<SortAscIcon v-if="sortKey === 'title' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'title' && sortOrder !== 1" />
							</span>
						</th>
						<th @click="sortBy('lastModified')">
							<span class="column-title">
								{{ t('news', 'Last update') }}
								<SortAscIcon v-if="sortKey === 'lastModified' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'lastModified' && sortOrder !== 1" />
							</span>
						</th>
						<th @click="sortBy('nextUpdateTime')">
							<span class="column-title">
								{{ t('news', 'Next update') }}
								<SortAscIcon v-if="sortKey === 'nextUpdateTime' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'nextUpdateTime' && sortOrder !== 1" />
							</span>
						</th>
						<th :title="t('news', 'Articles per update')"
							@click="sortBy('articlesPerUpdate')">
							<span class="column-title">
								APU
								<SortAscIcon v-if="sortKey === 'articlesPerUpdate' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'articlesPerUpdate' && sortOrder !== 1" />
							</span>
						</th>
						<th :title="t('news', 'Error Count') "
							@click="sortBy('updateErrorCount')">
							<span class="column-title">
								EC
								<SortAscIcon v-if="sortKey === 'updateErrorCount' && sortOrder === 1" />
								<SortDescIcon v-if="sortKey === 'updateErrorCount' && sortOrder !== 1" />
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="feed in sortedFeeds" :key="feed.id">
						<td>{{ feed.id }}</td>
						<td>{{ feed.title }}</td>
						<td>{{ formatDate(feed.lastModified/1000) }}</td>
						<td>{{ formatDate(feed.nextUpdateTime*1000) }}</td>
						<td>{{ feed.articlesPerUpdate }}</td>
						<td :title="feed.lastUpdateError">
							{{ feed.updateErrorCount }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</NcModal>
</template>

<script>
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import SortAscIcon from 'vue-material-design-icons/SortAscending.vue'
import SortDescIcon from 'vue-material-design-icons/SortDescending.vue'
import { mapState } from 'vuex'

export default {
	name: 'FeedInfoTable',
	components: {
		NcModal,
		SortAscIcon,
		SortDescIcon,
	},
	data() {
		return {
			sortKey: 'title',
			sortOrder: 1,
		}
	},
	computed: {
		...mapState({
			feeds: state => state.feeds.feeds,
		}),
		sortedFeeds() {
			const sorted = this.feeds
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
			padding: .75rem 1rem .75rem 0;
			border-bottom: 2px solid var(--color-background-darker);
			&:hover {
				background-color: var(--color-background-hover);
			}
		}

		td {
			padding: .75rem 1rem .75rem 0;
			border-top: 1px solid var(--color-background-dark);
			border-bottom: unset;

			&.noborder {
				border-top: unset;
			}

			&.ellipsis_top {
				padding-bottom: 0;
			}

			&.ellipsis {
				padding-top: 0;
				padding-bottom: 0;
			}

			&.ellipsis_bottom {
				padding-top: 0;
			}
		}

		.column-title {
			display: flex;
			align-items: center;
			gap: 4px;
		}

	}
</style>
