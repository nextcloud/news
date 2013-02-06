module.exports = (grunt) ->
	
	grunt.loadNpmTasks('grunt-contrib-coffee')

	grunt.initConfig
	
		meta:
			pkg: '<json:package.json>'
			version: '<config:meta.pkg.version>'
			banner: '/*! <%= meta.pkg.description %> - v<%= meta.version %> - ' +
				'<%= grunt.template.today("yyyy-mm-dd") %>\n' +
				' * https://github.com/owncloud/apps\n' +
				'<% _.forEach(meta.pkg.contributors, function(contributor){	%>' +
				' * Copyright (c) <%= grunt.template.today("yyyy") %> ' +
				'<%= contributor.name %> <<%= contributor.email %>>\n' +
				'<% };) %>' +
				' * Licensed AGPL \n' + 
				' */'
			prefix: '(function(angular, $, OC, oc_requesttoken){'
			suffix: '})(window.angular, jQuery, OC, oc_requesttoken);'
			build: 'build/'
			production: '../js/'

		concat:
			app: 
				src: [
						'<banner:meta.prefix>'
						'<%= meta.build %>main.js'
						'<banner:meta.suffix>'
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
