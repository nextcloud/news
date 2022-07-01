module.exports = {
	root: true,
	parser: 'vue-eslint-parser',
	parserOptions: {
		parser: {
			ts: '@typescript-eslint/parser',
		},
		ecmaVersion: 2020,
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
	rules: {
		'node/no-unpublished-import': 'off', // necessary for vue-property-decorator (not published?)
	},
}
