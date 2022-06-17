const webpackConfig = require('@nextcloud/webpack-vue-config')

// set mode to production if `npm run build` called
webpackConfig.mode = process.env.NODE_ENV === 'production' ? 'production' : 'development'

// Add Babel Loader before TS Loader to process typescript (babel is needed for decorators for some reason?)
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
