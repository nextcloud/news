const path = require('path');

module.exports = {
	root: true,
	parser: 'vue-eslint-parser',
	parserOptions: {
		parser: {
			ts: '@typescript-eslint/parser',
		},
		ecmaVersion: 2020,
	},
	env: {
		jest: true,
	},
	rules: {
		'n/no-missing-import': {
			resolvePaths: [
				path.resolve(__dirname, '/src/'), 
				path.resolve(__dirname, '/node_modules/')
			],
		},
	},
	extends: [
		'eslint:recommended',
		'plugin:vue/base',
		'plugin:vue/essential',
		'@vue/standard',
		'@vue/typescript/recommended',
		'@nextcloud',
	],
	ignorePatterns: ['*.d.ts'],
}
