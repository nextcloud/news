/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
module.exports = function (grunt) {
    'use strict';

    // load needed modules
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-wrap');
    grunt.loadNpmTasks('grunt-karma');
    grunt.loadNpmTasks('grunt-ng-annotate');
    grunt.loadNpmTasks('grunt-protractor-runner');
    grunt.loadNpmTasks('grunt-protractor-webdriver');

    grunt.initConfig({
        meta: {
            pkg: grunt.file.readJSON('package.json'),
            version: '<%= meta.pkg.version %>',
            production: 'build/'
        },
        concat: {
            options: {
                // remove license headers
                stripBanners: true
            },
            dist: {
                src: [
                    'app/App.js',
                    'app/Config.js',
                    'app/Run.js',
                    'controller/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'gui/**/*.js',
                    'utility/**/*.js',
                    'directive/**/*.js'
                ],
                dest: '<%= meta.production %>app.js'
            }
        },
        ngAnnotate: {
            app: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js'
            }
        },
        uglify: {
            app: {
                files: {
                    '<%= meta.production %>app.min.js':
                        ['<%= meta.production %>app.js']
                }
            },
            options: {
            }
        },
        cssmin: {
            newsBackport: {
                files: {'../css/news-owncloud7.min.css': [
                    '../css/7.css',
                    '../css/app.css',
                    '../css/content.css',
                    '../css/custom.css',
                    '../css/mobile.css',
                    '../css/shortcuts.css',
                    '../css/navigation.css',
                    '../css/settings.css'
                ]}
            },
            news: {
                files: {'../css/news.min.css': [
                    '../css/app.css',
                    '../css/content.css',
                    '../css/custom.css',
                    '../css/shortcuts.css',
                    '../css/mobile.css',
                    '../css/navigation.css',
                    '../css/settings.css'
                ]}
            }
        },
        wrap: {
            basic: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js',
                options: {
                    wrapper: [
                        '(function(window, document, angular, $, OC, ' +
                            'csrfToken, undefined){\n\n\'use strict\';\n\n',

                        '\n})(window, document, angular, jQuery, OC, ' +
                            'oc_requesttoken);'
                    ]
                }
            }
        },
        jshint: {
            app: {
                src: [
                    'Gruntfile.js',
                    'app/App.js',
                    'app/Config.js',
                    'app/Run.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'controller/**/*.js',
                    'directive/**/*.js',
                    'tests/**/*.js',
                    'gui/**/*.js'
                ]
            },
            options: {
                jshintrc: true
            }
        },
        watch: {
            concat: {
                files: [
                    '../css/*.css',
                    '!../css/*.min.css',
                    'tests/**/*.js',
                    'app/**/*.js',
                    'controller/**/*.js',
                    'utility/**/*.js',
                    'directive/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'gui/**/*.js',
                    '../templates/**/*.php'
                ],
                tasks: ['default'],
                options: {
                    livereload: true
                }
            },
            phpunit: {
                files: [
                    '../**/*.php'
                ],
                tasks: ['phpunit']
            }
        },
        karma: {
            unit: {
                configFile: 'karma.conf.js',
                autoWatch: true
            },
            continuous: {
                configFile: 'karma.conf.js',
                browsers: ['Firefox'],
                singleRun: true,
            }
        },
        phpunit: {
            classes: {
                dir: '../tests'
            },
            options: {
                colors: true,
                configuration: '../phpunit.xml'
            }
        },
        /* jshint camelcase: false */
        protractor_webdriver: {
            app: {

            }
        },
        protractor: {
            firefox: {
                options: {
                    configFile: 'protractor.conf.js'
                }
            },
        },
        connect: {
            server: {
                options: {
                    base: 'tests/static/'
                }
            }
        }
    });

    // make tasks available under simpler commands
    grunt.registerTask('default', ['jshint', 'concat',  'wrap', 'ngAnnotate',
                                   'uglify', 'cssmin']);
    grunt.registerTask('dev', ['watch:concat']);
    grunt.registerTask('test', ['karma:unit']);
    grunt.registerTask('php', ['watch:phpunit']);
    grunt.registerTask('e2e', ['protractor_webdriver', 'connect',
                               'protractor']);
    grunt.registerTask('ci-unit', ['default', 'karma:continuous']);
    grunt.registerTask('ci-e2e', ['protractor_webdriver', 'connect',
                                  'protractor']);
};