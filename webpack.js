const { merge } = require('webpack-merge')
let webpackConfig = require('@nextcloud/webpack-vue-config')
const path = require('path')

webpackConfig.entry['admin-settings'] = path.join(
	__dirname,
	'src',
	'main-admin.js',
)

webpackConfig = merge(webpackConfig, {
	resolve: {
		extensions: ['.ts'],
	},
})

// Add TS Loader for processing typescript in vue templates
webpackConfig.module.rules.push({
	test: /.ts$/,
	exclude: [/node_modules/],
	use: [
		{
			loader: 'ts-loader',
			options: {
				transpileOnly: true,
				appendTsSuffixTo: ['\\.vue$'],
			},
		},
	],
})

module.exports = webpackConfig
