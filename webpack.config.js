// SPDX-FileCopyrightText: Carl Schwan <carl@carlschwan.eu>
// SPDX-License-Identifier: AGPL-3.0-or-later

const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	'admin-settings': path.join(__dirname, 'src', 'main-admin.js'),
}
webpackConfig.output.path = path.resolve('./js/build/')
webpackConfig.output.publicPath = path.join('/apps/', process.env.npm_package_name, '/js/build/')

module.exports = webpackConfig
