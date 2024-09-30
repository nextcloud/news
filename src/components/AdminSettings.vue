<!--
SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
SPDX-Licence-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection :title="t('news', 'News')"
		class="news-settings"
		doc-url="https://nextcloud.github.io/news/admin/">
		<template v-if="lastCron !== 0">
			<NcNoteCard v-if="oldExecution" type="error">
				{{ t('news', 'Last job execution ran {relativeTime}. Something seems wrong.', {relativeTime}) }}
			</NcNoteCard>

			<NcNoteCard v-else type="success">
				{{ t('news', 'Last job ran {relativeTime}.', {relativeTime}) }}
			</NcNoteCard>
		</template>
		<div class="field">
			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="useCronUpdates"
				@update:checked="update('useCronUpdates', useCronUpdates)">
				{{ t("news", "Use system cron for updates") }}
			</NcCheckboxRadioSwitch>
		</div>
		<p class="settings-hint">
			{{ t("news", "Disable this if you use a custom updater.") }}
		</p>

		<div class="field">
			<NcTextField :value.sync="autoPurgeCount"
				:label="t('news', 'Maximum read count per feed')"
				:label-visible="true"
				@update:value="update('autoPurgeCount', autoPurgeCount)" />
		</div>
		<p class="settings-hint">
			{{ t( "news", "Defines the maximum amount of articles that can be read per feed which will not be deleted by the cleanup job; if old articles reappear after being read, increase this value; negative values such as -1 will turn this feature off.") }}
		</p>

		<div class="field">
			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="purgeUnread"
				@update:checked="update('purgeUnread', purgeUnread)">
				{{ t("news", "Delete unread articles automatically") }}
			</NcCheckboxRadioSwitch>
		</div>
		<p class="settings-hint">
			{{ t( "news", "Enable this if you also want to delete unread articles.") }}
		</p>

		<div class="field">
			<NcTextField :value.sync="maxRedirects"
				:label="t('news', 'Maximum redirects')"
				:label-visible="true"
				@update:value="update('maxRedirects', maxRedirects)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "How many redirects the feed fetcher should follow.") }}
		</p>

		<div class="field">
			<NcTextField :value.sync="feedFetcherTimeout"
				:label="t('news', 'Feed fetcher timeout')"
				:label-visible="true"
				@update:value="update('feedFetcherTimeout', feedFetcherTimeout)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "Maximum number of seconds to wait for an RSS or Atom feed to load; if it takes longer the update will be aborted.") }}
		</p>

		<div class="field">
			<NcTextField :value.sync="exploreUrl"
				:label="t('news', 'Explore Service URL')"
				:label-visible="true"
				@update:value="update('exploreUrl', exploreUrl)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "If given, this service's URL will be queried for displaying the feeds in the explore feed section. To fall back to the built in explore service, leave this input empty.") }}
		</p>

		<div class="field">
			<NcTextField :value.sync="updateInterval"
				:label="t('news', 'Update interval')"
				:label-visible="true"
				@update:value="update('updateInterval', updateInterval)" />
		</div>
		<p class="settings-hint">
			{{ t("news", "Interval in seconds in which the feeds will be updated.") }}
		</p>
	</NcSettingsSection>
</template>

<script>
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'
import moment from '@nextcloud/moment'
import { loadState } from '@nextcloud/initial-state'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { confirmPassword } from '@nextcloud/password-confirmation'

/**
 * Debounce helper for method
 * TODO: Should we remove this and use library?
 *
 * @param {Function} func - The callback function
 * @param {number}     wait - Time to wait in milliseconds
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
			relativeTime: moment(lastCron * 1000).fromNow(),
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
			if (key === 'useCronUpdates' || key === 'purgeUnread') {
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
