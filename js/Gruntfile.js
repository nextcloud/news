/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

var globals = [
    // libs
    '$',
    'angular',
    // app
    'app',
    'OC',
    // angular
    'inject',
    'module',

    // protractor
    'protractor',
    'browser',
    'By',
    // jasmine
    'jasmine',
    'it',
    'describe',
    'beforeEach',
    'expect',
    // js
    'console',
    'exports'
];

module.exports = function (grunt) {
    'use strict';

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
                    'app/app.js',
                    'app/config.js',
                    'app/run.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'directive/**/*.js'
                ],
                dest: '<%= meta.production %>app.js'
            }
        },
        ngmin: {
            app: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js'
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
                    'app/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    'directive/**/*.js',
                    'tests/**/*.js',
                    'Gruntfile.js',
                    'karma.conf.js',
                    'protractor*conf.js'
                ],
                directives: {
                    browser: true,
                    predef: globals
                }
            }
        },
        watch: {
            concat: {
                files: [
                    'tests/**/*.js',
                    'app/**/*.js',
                    'controller/**/*.js',
                    'directive/**/*.js',
                    'filter/**/*.js',
                    'service/**/*.js',
                    '../templates/**/*.php'
                ],
                options: {
                    livereload: true
                },
                tasks: ['default']
            },
            phpunit: {
                files: [
                    '../*/**.php',
                    '!../3rdparty'
                ],
                tasks: ['phpunit']
            }
        },
        karma: {
            unit: {
                configFile: 'karma.conf.js',
                browsers: ['PhantomJS'],
                autoWatch: true
            },
            continuous: {
                configFile: 'karma.conf.js',
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
                    configFile: 'protractor.phantomjs.conf.js'
                }
            },
            chrome: {
                options: {
                    configFile: 'protractor.chrome.conf.js'
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
    grunt.registerTask('e2e', ['protractor_webdriver', 'connect', 'protractor:chrome']);
    grunt.registerTask('ci-unit', ['default', 'karma:continuous']);
    grunt.registerTask('ci-e2e', ['protractor_webdriver', 'connect', 'protractor:phantomjs']);
};