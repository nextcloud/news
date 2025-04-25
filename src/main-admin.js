// SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
// SPDX-Licence-Identifier: AGPL-3.0-or-later

import { createApp } from 'vue'
import { translate as t } from '@nextcloud/l10n'

import AdminSettings from './components/AdminSettings.vue'

const app = createApp(AdminSettings)

app.config.globalProperties.t = t

app.mount('#vue-admin-news')
