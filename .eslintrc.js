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
        'vue/html-indent': 'off', // TODO: remove this during reformat (expects tab char \t but right now code base uses spaces)
        indent: ['error', 4],
        'node/no-unpublished-import': 'off',
    },
}
