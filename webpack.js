const webpackConfig = require('@nextcloud/webpack-vue-config')

const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin')

// TODO Make proper based on command:
webpackConfig.mode = 'development'


delete webpackConfig.module.rules[2].loader
webpackConfig.module.rules[2].use = [
	'vue-loader',
]

webpackConfig.resolve.extensions.push('.tsx')
webpackConfig.resolve.modules = ['node_modules']

webpackConfig.plugins.push(new ForkTsCheckerWebpackPlugin({
	typescript: {
		extensions: {
			vue: {
				enabled: true,
				compiler: 'vue-template-compiler',
			},
		},
	},
}))

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
