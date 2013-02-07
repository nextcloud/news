module.exports = (grunt) ->
	
	grunt.loadNpmTasks('grunt-contrib-coffee')

	grunt.initConfig
	
		meta:
			pkg: grunt.file.readJSON('package.json>')
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
				src: [	
						'<%= meta.banner %>'
						'<%= meta.prefix %>'
						'<%= meta.build %>main.js'
						'<%= meta.suffix %>'
					]
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

		watch: 
			app: 
				files: './**/*.coffee',
				tasks: 'compile'


	grunt.registerTask('run', 'watch')
	grunt.registerTask('compile', 'concat:owncloud concat:news coffee concat:app')
