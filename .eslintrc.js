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
		 // frustratingly this seems to error for all imports right now...
		'n/no-missing-import': 'off',

		// need to warn on these because @nextcloud repeats some component names (Button, Content..)
		'vue/no-reserved-component-names': 'warn',
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
