let webpackConfig = require('@nextcloud/webpack-vue-config')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')
const path = require('path')

webpackConfig.entry['admin-settings'] = path.join(
	__dirname,
	'src',
	'main-admin.js',
)

webpackConfig.entry['cron-warning'] = path.join(
	__dirname,
	'src',
	'main-cron-warning.js',
)

// Add TS Loader for processing typescript in vue templates
webpackRules.RULE_TS = {
	test: /.ts$/,
	exclude: [/node_modules/],
	loader: 'ts-loader',
	options: {
		transpileOnly: true,
		appendTsSuffixTo: ['\\.vue$'],
	},
}

webpackConfig.module.rules = Object.values(webpackRules)

module.exports = webpackConfig
