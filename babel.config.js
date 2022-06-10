module.exports = {
    plugins: [
        '@babel/plugin-syntax-dynamic-import',
        ['@babel/proposal-decorators', { legacy: true }],
        ['@babel/proposal-class-properties', { loose: true }],
        ['@babel/plugin-proposal-private-methods', { loose: true }],
        ['@babel/plugin-proposal-private-property-in-object', { loose: true }],
    ],
    presets: ['@babel/preset-env'],
}
