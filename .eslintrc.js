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

        // TODO: remove these indentation rules during reformat (expects tab char \t but right now code base uses spaces)
        'vue/html-indent': 'off',
        indent: ['error', 4],
    },
}
