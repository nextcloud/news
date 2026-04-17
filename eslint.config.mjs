/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: CC0-1.0
 */
import { recommended } from '@nextcloud/eslint-config'

export default [
	...recommended,
	{
		rules: {
			// allow console.error()
			'no-console': ["error", { allow: ["error"] }],
		},
	},
]
