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
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-wrap');
    grunt.loadNpmTasks('grunt-karma');
    grunt.loadNpmTasks('grunt-ngmin');
    grunt.loadNpmTasks('grunt-traceur');
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
        ngmin: {
            app: {
                src: ['<%= meta.production %>app.js'],
                dest: '<%= meta.production %>app.js'
            }
        },
        traceur: {
            app: {
                files: {
                    '<%= meta.production %>app.js': ['<%= meta.production %>app.js']
                }
            },
            options: {
                blockBinding: true,
                sourceMap: false,
                experimental: true,
                modules: 'inline'
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
                    '../*/**.php',
                    '!../3rdparty'
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
    grunt.registerTask('default', ['jshint', 'concat',  'wrap', 'traceur', 'ngmin']);
    grunt.registerTask('dev', ['watch:concat']);
    grunt.registerTask('test', ['karma:unit']);
    grunt.registerTask('phpunit', ['watch:phpunit']);
    grunt.registerTask('e2e', ['protractor_webdriver', 'connect', 'protractor:chrome']);
    grunt.registerTask('ci-unit', ['default', 'karma:continuous']);
    grunt.registerTask('ci-e2e', ['protractor_webdriver', 'connect', 'protractor:phantomjs']);
};