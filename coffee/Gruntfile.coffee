###
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###

module.exports = (grunt) ->
	
	grunt.loadNpmTasks('grunt-contrib-coffee')
	grunt.loadNpmTasks('grunt-contrib-concat')
	grunt.loadNpmTasks('grunt-contrib-watch')
	grunt.loadNpmTasks('grunt-coffeelint')
	grunt.loadNpmTasks('gruntacular');

	grunt.initConfig
	
		meta:
			pkg: grunt.file.readJSON('package.json')
			version: '<%= meta.pkg.version %>'
			banner: '/**\n' +
				' * <%= meta.pkg.description %> - v<%= meta.version %>\n' +
				' *\n' +
				'<% _.forEach(meta.pkg.contributors, function(contributor){	%>' +
				' * Copyright (c) <%= grunt.template.today("yyyy") %> - ' +
				'<%= contributor.name %> <<%= contributor.email %>>\n' +
				'<% }) %>' +
				' *\n' +
				' * This file is licensed under the Affero General Public License version 3 or later.\n' +
				' * See the COPYING-README file\n' +
				' *\n' + 
				' */'
			prefix: '(function(angular, $, OC, oc_requesttoken){'
			suffix: '})(window.angular, jQuery, OC, oc_requesttoken);'
			build: 'build/'
			production: '../js/'

		concat:
			app: 
				options:
					banner: '<%= meta.banner %>\n'
				src: '<%= meta.build %>main.js'
				dest: '<%= meta.production %>app.js'
			owncloud: 
				src: ['lib/owncloud.coffee', 'lib/services/*.coffee']
				dest: '<%= meta.build %>owncloud.coffee'
			news: 
				src: [
						'app.coffee'
						'services/*.coffee'
						'controllers/*.coffee'
						'directives/*.coffee'
						'filters/*.coffee'
					]
				dest: '<%= meta.build %>news.coffee'
			
		coffee: 
			compile:
				files:
					'<%= meta.build %>main.js': [
						'<%= meta.build %>owncloud.coffee'
						'<%= meta.build %>news.coffee'
					]
		coffeelint:
			app: [
				'app.coffee'
				'services/*.coffee'
				'controllers/*.coffee'
				'directives/*.coffee'
				'filters/*.coffee'
				'lib/**/*.coffee'
			]
		coffeelintOptions:
			'no_tabs':
				'level': 'ignore'
			'indentation':
				'level': 'ignore'

		watch: 
			app: 
				files: './**/*.coffee',
				tasks: 'compile'


	grunt.registerTask('run', ['watch'])
	grunt.registerTask('lint', ['coffeelint'])
	grunt.registerTask('compile', [
			#'coffeelint'
			'concat:owncloud'
			'concat:news'
			'coffee'
			'concat:app'
			]
	)
