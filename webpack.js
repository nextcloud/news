const webpackConfig = require('@nextcloud/webpack-vue-config')

// Add Babel Loader before TS Loader for processing typescript in vue templates (babel is needed for decorators for some reason?)
webpackConfig.module.rules.push({
    test: /.ts$/,
    exclude: [/node_modules/],
    use: [
        'babel-loader',
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
