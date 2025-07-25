<!--
SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
SPDX-Licence-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection
		:name="t('news', 'News')"
		class="news-settings"
		doc-url="https://nextcloud.github.io/news/admin/">
		<div class="field">
			<NcNoteCard v-if="lastCron === 0" type="warning">
				{{ t('news', 'No job execution data available. The cron job may not be running properly.') }}
			</NcNoteCard>

			<NcNoteCard v-else-if="oldExecution" type="error">
				{{ t('news', 'Last job execution ran {relativeTime}. Something is wrong.', { relativeTime }) }}
			</NcNoteCard>

			<NcNoteCard v-else type="success">
				{{ t('news', 'Last job ran {relativeTime}.', { relativeTime }) }}
			</NcNoteCard>
		</div>
		<div class="field">
			<NcCheckboxRadioSwitch
				v-model:model-value="useCronUpdates"
				type="switch"
				@update:model-value="update('useCronUpdates', useCronUpdates)">
				{{ t("news", "Use system cron for updates") }}
			</NcCheckboxRadioSwitch>
		</div>
		<p class="settings-hint">
			{{ t("news", "Disable this if you use a custom updater.") }}
		</p>

		<div class="field">
			<NcTextField
				v-model:model-value="autoPurgeCount"
				:label="t('news', 'Maximum read count per feed')"
				:label-visible="true"
				@update:model-value="update('autoPurgeCount', autoPurgeCount)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "Defines the maximum amount of articles that can be read per feed which will not be deleted by the cleanup job; if old articles reappear after being read, increase this value; negative values such as -1 will turn this feature off.") }}
		</p>

		<div class="field">
			<NcCheckboxRadioSwitch
				v-model:model-value="purgeUnread"
				type="switch"
				@update:model-value="update('purgeUnread', purgeUnread)">
				{{ t("news", "Delete unread articles automatically") }}
			</NcCheckboxRadioSwitch>
		</div>
		<p class="settings-hint">
			{{ t("news", "Enable this if you also want to delete unread articles.") }}
		</p>

		<div class="field">
			<NcTextField
				v-model:model-value="maxRedirects"
				:label="t('news', 'Maximum redirects')"
				:label-visible="true"
				@update:model-value="update('maxRedirects', maxRedirects)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "How many redirects the feed fetcher should follow.") }}
		</p>

		<div class="field">
			<NcTextField
				v-model:model-value="feedFetcherTimeout"
				:label="t('news', 'Feed fetcher timeout')"
				:label-visible="true"
				@update:model-value="update('feedFetcherTimeout', feedFetcherTimeout)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "Maximum number of seconds to wait for an RSS or Atom feed to load; if it takes longer the update will be aborted.") }}
		</p>

		<div class="field">
			<NcTextField
				v-model:model-value="exploreUrl"
				:label="t('news', 'Explore Service URL')"
				:label-visible="true"
				@update:model-value="update('exploreUrl', exploreUrl)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "If provided, the URL of this service will be queried to display the feeds in the explore feed section. To fall back to the built in explore service, leave this input empty.") }}
		</p>

		<div class="field">
			<NcTextField
				v-model:model-value="updateInterval"
				:label="t('news', 'Update interval')"
				:label-visible="true"
				@update:model-value="update('updateInterval', updateInterval)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "Interval in seconds in which the feeds will be updated.") }}
		</p>

		<div class="field">
			<NcCheckboxRadioSwitch
				v-model:model-value="useNextUpdateTime"
				type="switch"
				@update:model-value="update('useNextUpdateTime', useNextUpdateTime)">
				{{ t("news", "Use next update time for feed updates") }}
			</NcCheckboxRadioSwitch>
		</div>
		<p class="settings-hint">
			{{ t("news", "Enable this to use the calculated next update time for feed updates. Disable to update feeds based solely on the update interval.") }}
		</p>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { confirmPassword } from '@nextcloud/password-confirmation'
import { generateOcsUrl } from '@nextcloud/router'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import { formatDateRelative } from '../utils/dateUtils.ts'

/**
 * Debounce helper for method
 * TODO: Should we remove this and use library?
 *
 * @param {Function} func - The callback function
 * @param {number} wait - Time to wait in milliseconds
 */
function debounce(func, wait) {
	let timeout

	return function executedFunction(...args) {
		clearTimeout(timeout)
		timeout = setTimeout(() => {
			func.apply(this, args)
		}, wait)
	}
}

const successMessage = debounce(() => showSuccess(t('news', 'Successfully updated news configuration')), 500)
const lastCron = loadState('news', 'lastCron')

export default {
	name: 'AdminSettings',
	components: {
		NcCheckboxRadioSwitch,
		NcSettingsSection,
		NcTextField,
		NcNoteCard,
	},

	data() {
		return {
			useCronUpdates: loadState('news', 'useCronUpdates') === '1',
			autoPurgeCount: loadState('news', 'autoPurgeCount'),
			purgeUnread: loadState('news', 'purgeUnread') === '1',
			maxRedirects: loadState('news', 'maxRedirects'),
			feedFetcherTimeout: loadState('news', 'feedFetcherTimeout'),
			exploreUrl: loadState('news', 'exploreUrl'),
			updateInterval: loadState('news', 'updateInterval'),
			useNextUpdateTime: loadState('news', 'useNextUpdateTime') === '1',
			relativeTime: formatDateRelative(lastCron),
			lastCron,
		}
	},

	computed: {
		oldExecution() {
			return Date.now() / 1000 - this.lastCron > (parseInt(this.updateInterval) * 2) + 900
		},
	},

	methods: {
		async update(key, value) {
			await confirmPassword()
			const url = generateOcsUrl(
				'/apps/provisioning_api/api/v1/config/apps/{appId}/{key}',
				{
					appId: 'news',
					key,
				},
			)
			if (key === 'useCronUpdates' || key === 'purgeUnread' || key === 'useNextUpdateTime') {
				value = value ? '1' : '0'
			}
			try {
				const { data } = await axios.post(url, {
					value,
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

		handleResponse({ status, errorMessage, error }) {
			if (status !== 'ok') {
				showError(errorMessage)
				console.error(errorMessage, error)
			} else {
				successMessage()
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.news-settings {
	p {
		max-width: 700px;
		margin-top: 0.25rem;
		margin-bottom: 1rem;
	}

	.input-field {
		max-width: 350px;
	}
}
</style>
