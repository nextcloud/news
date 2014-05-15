/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

module.exports = function(grunt) {

    // load needed modules
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-wrap');
    grunt.loadNpmTasks('grunt-karma');
    grunt.loadNpmTasks('grunt-ngmin');
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
                    'config/app.js',
                    'config/config.js',
                    'config/run.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'directive/**/*.js',
                    'utilitie/**/*.js'
                ],
                dest: '<%= meta.production %>app.js'
            }
        },
        ngmin: {
            app: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js',
            }
        },
        wrap: {
            basic: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js',
                options: {
                    wrapper: [
                        '(function(angular, $, OC, undefined){\n\n\'use strict\';\n\n',
                        '\n})(angular, jQuery, OC);'
                    ]
                }
            }
        },
        jslint: {
            browser: {
                src: [
                    'tests/**/*.js',
                    'config/app.js',
                    'config/config.js',
                    'config/run.js',
                    'controller/**/*.js',
                    'directive/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                ],
                directives: {
                    browser: true,
                    predef: [
                        '$', 'angular', 'app', 'OC',
                        'protractor', 'describe', 'beforeEach', 'module', 'it',
                        'browser', 'expect', 'By', 'inject'
                    ]
                }
            }
        },
        watch: {
            concat: {
                files: [
                    'tests/**/*.js',
                    'config/**/*.js',
                    'controller/**/*.js',
                    'directive/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                ],
                tasks: ['default']
            },
            phpunit: {
                files: [
                    '../*/**.php',
                    '!../3rdparty',
                ],
                tasks: ['phpunit']
            }
        },
        karma: {
            unit: {
                configFile: 'config/karma.js',
                browsers: ['PhantomJS'],
                autoWatch: true
            },
            continuous: {
                configFile: 'config/karma.js',
                singleRun: true,
                browsers: ['PhantomJS'],
                preprocessors: {
                    'build/app.js': 'coverage'
                },
                coverageReporter: {
                    type: 'lcovonly',
                    dir: 'coverage/',
                    file: 'coverage.lcov'
                },
                reporters: ['coverage']
            }
        },
        phpunit: {
            classes: {
                dir: '../tests'
            },
            options: {
                colors: true
            }
        },
        protractor_webdriver: {
            app: {

            }
        },
        protractor: {
            phantomjs: {
                options: {
                    configFile: 'config/protractor.js'
                }
            }
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
    grunt.registerTask('default', ['jslint', 'concat', 'ngmin', 'wrap']);
    grunt.registerTask('test', ['karma:unit']);
    grunt.registerTask('ci', ['default', 'karma:continuous']);
    grunt.registerTask('e2e', ['protractor_webdriver', 'connect', 'protractor']);

};