const { merge } = require('webpack-merge')
const webpack = require('webpack')
const webpackConfig = require('@nextcloud/webpack-vue-config')

const config = {
    module: {
        rules: [
            {
                test: /\.(jpg|png|svg)$/,
                use: {
                    loader: 'url-loader',
                },
            }
        ],
    },
}

module.exports = merge(config, webpackConfig)