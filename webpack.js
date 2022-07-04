const webpackConfig = require('@nextcloud/webpack-vue-config')

// Add TS Loader for processing typescript in vue templates
webpackConfig.module.rules.push({
    test: /.ts$/,
    exclude: [/node_modules/],
    use: [
        {
            loader: 'ts-loader',
            options: {
                transpileOnly: true,
                appendTsSuffixTo: [
                    '\\.vue$',
                ],
            },
        },
    ],
})

module.exports = webpackConfig
