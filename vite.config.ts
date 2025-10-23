/*!
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: CC0-1.0
 */

import { createAppConfig } from '@nextcloud/vite-config'
import { resolve } from 'node:path'

export default createAppConfig({
	main: 'src/main.js',
	'admin-settings': 'src/main-admin.js',
	'cron-warning': 'src/main-cron-warning.js',
}, {
	inlineCSS: { relativeCSSInjection: true },
	config: {
		build: {
			cssCodeSplit: true,
		},
		test: {
			coverage: {
				include: ['src/**/*.ts', 'src/**/*.vue'],
				provider: 'istanbul',
				reporter: ['lcov', 'text'],
			},
			environment: 'jsdom',
			setupFiles: resolve(__dirname, './tests/javascript/unit/setup.js'),
			server: {
				deps: {
					inline: [
						// Fix unresolvable .css extension for ssr
						/@nextcloud\/vue/,
						/@nextcloud\/dialogs/,
					],
				},
			},
		},
	}
})

