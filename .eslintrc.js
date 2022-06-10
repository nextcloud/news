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
        'jsdoc/check-alignment': 'off',
        'vue/html-indent': 'off',
        indent: ['error', 4],
        'node/no-unpublished-import': 'off',
    },
}
