let webpackConfig = require('@nextcloud/webpack-vue-config')
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
