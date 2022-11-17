// SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
// SPDX-Licence-Identifier: AGPL-3.0-or-later

import Vue from 'vue'
import { getRequestToken } from '@nextcloud/auth'
// import { translate as t } from '@nextcloud/l10n'

import AdminSettings from './components/AdminSettings.vue'

// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken());

Vue.mixin({
	methods: {
		t,
	},
})

const AdminSettingsView = Vue.extend(AdminSettings)
new AdminSettingsView().$mount('#vue-admin-news')
