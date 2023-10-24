const stylelintConfig = require('@nextcloud/stylelint-config')

stylelintConfig.rules.indentation = 4
stylelintConfig.ignoreFiles.push('**/*.vue')

module.exports = stylelintConfig
