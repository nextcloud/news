const webpackConfig = require('@nextcloud/webpack-vue-config')
const { webpack } = require('webpack')

webpackConfig.mode = 'development'
delete webpackConfig.module.rules[2].loader
webpackConfig.module.rules[2].use = [
	'vue-loader',
]

webpackConfig.resolve.extensions.push('.tsx')
webpackConfig.resolve.modules = ['node_modules']

// eslint-disable-next-line no-console
console.log(JSON.stringify(webpackConfig, undefined, 2))

// eslint-disable-next-line no-console
console.log(webpackConfig.module.rules)

module.exports = webpackConfig
