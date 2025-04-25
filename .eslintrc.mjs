module.exports = {
	extends: [
		'@nextcloud',
		'@nextcloud/eslint-config/typescript',
		'@nextcloud/eslint-config/vue3',
	],
	rules: {
		'no-console': 'warn',
		'@typescript-eslint/no-var-requires': 'off',

		// disable deprecated func-call-spacing
		'@typescript-eslint/func-call-spacing': 'off',

		// TODO: Trouble importing .ts files into .vue files for some reason?
		'import/extensions': 'off',
		'n/no-missing-import': 'off',
	},
	settings: {
		'import/resolver': {
			node: {
				extensions: ['.ts'],
			},
		},
	},
	overrides: [
		{
		 files: ['*spec.ts', 'tests/javascript/unit/setup.ts'],
			rules: {
				'@typescript-eslint/no-explicit-any': 'off',
			},
		},
		{
			files: ['src/store/*.ts'],
			rules: {
				'function-paren-newline': ['error', 'multiline'],
			},
		},
	 ],
}
