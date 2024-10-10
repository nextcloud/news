<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcModal size="normal"
		data-text-el="formatting-help"
		:name="t('news', 'Keyboard shortcuts')"
		@close="$emit('close')">
		<div class="help-modal">
			<h2>{{ t('news', 'Keyboard shortcuts') }}</h2>
			<table>
				<thead>
					<tr>
						<th>{{ t('news', 'Keyboard shortcut') }}</th>
						<th>{{ t('news', 'Action') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<kbd>n</kbd>
							/
							<kbd>j</kbd>
							/
							<kbd>{{ t('news', 'right') }}</kbd>
						</td>
						<td>
							{{ t('news', 'Jump to next article') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>p</kbd>
							/
							<kbd>k</kbd>
							/
							<kbd>{{ t('news', 'left') }}</kbd>
						</td>
						<td>
							{{ t('news', 'Jump to previous article') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>s</kbd>
							/
							<kbd>l</kbd>
						</td>
						<td>
							{{ t('news', 'Toggle star article') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>h</kbd>
						</td>
						<td>
							{{ t('news', 'Star article and jump to next one') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>u</kbd>
						</td>
						<td>
							{{ t('news', 'Toggle keep current article unread') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>o</kbd>
						</td>
						<td>
							{{ t('news', 'Open article in new tab') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>e</kbd>
						</td>
						<td>
							{{ t('news', 'Toggle expand article in compact view') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>r</kbd>
						</td>
						<td>
							{{ t('news', 'Refresh') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>f</kbd>
						</td>
						<td>
							{{ t('news', 'Load next feed') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>d</kbd>
						</td>
						<td>
							{{ t('news', 'Load previous feed') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>c</kbd>
						</td>
						<td>
							{{ t('news', 'Load previous folder') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>v</kbd>
						</td>
						<td>
							{{ t('news', 'Load next folder') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>a</kbd>
						</td>
						<td>
							{{ t('news', 'Scroll to active navigation entry') }}
						</td>
					</tr>
					<tr>
						<td>
							<kbd>{{ t('news', 'Shift') }}</kbd>
							+
							<kbd>a</kbd>
						</td>
						<td>
							{{ t('news', 'Mark current articles feed/folder read') }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</NcModal>
</template>

<script>
import { NcModal, Tooltip } from '@nextcloud/vue'

export default {
	name: 'HelpModal',
	components: {
		NcModal,
	},
	directives: {
		Tooltip,
	},
}
</script>

<style lang="scss" scoped>
	.help-modal {
		width: max-content;
		padding: 30px 40px 20px;
		user-select: text;

		h2, h3 {
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
			font-weight: bold;
			padding: .75rem 1rem .75rem 0;
			border-bottom: 2px solid var(--color-background-darker);
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

		kbd {
			font-size: smaller;
		}

		code {
			padding: .2em .4em;
			font-size: 90%;
			background-color: var(--color-background-dark);
			border-radius: 6px;
		}
	}
</style>
