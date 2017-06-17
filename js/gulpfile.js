/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

/*jslint node: true */
'use strict';

const gulp = require('gulp'),
    ngAnnotate = require('gulp-ng-annotate'),
    uglify = require('gulp-uglify'),
    jshint = require('gulp-jshint'),
    KarmaServer = require('karma').Server,
    phpunit = require('gulp-phpunit'),
    concat = require('gulp-concat'),
    sourcemaps = require('gulp-sourcemaps');

// Configuration
const buildTarget = 'app.min.js';
const phpunitConfig = __dirname + '/../phpunit.xml';
const karmaConfig = __dirname + '/karma.conf.js';
const destinationFolder = __dirname + '/build/';
const sources = [
    'node_modules/es6-shim/es6-shim.min.js',
    'node_modules/angular/angular.min.js',
    'node_modules/angular-animate/angular-animate.min.js',
    'node_modules/angular-route/angular-route.min.js',
    'node_modules/angular-sanitize/angular-sanitize.min.js',
    'node_modules/moment/min/moment-with-locales.min.js',
    'node_modules/masonry-layout/dist/masonry.pkgd.min.js',
    'app/App.js', 'app/Config.js', 'app/Run.js',
    'controller/**/*.js',
    'filter/**/*.js',
    'service/**/*.js',
    'gui/**/*.js',
    'plugin/**/*.js',
    'utility/**/*.js',
    'directive/**/*.js'
];
const testSources = ['tests/**/*.js'];
const phpSources = ['../**/*.php', '!../js/**', '!../vendor/**'];
const watchSources = sources.concat(testSources).concat(['*.js']);
const lintSources = watchSources;

// tasks
gulp.task('default', ['lint'], () => {
    return gulp.src(sources)
        .pipe(ngAnnotate())
        .pipe(sourcemaps.init())
        .pipe(concat(buildTarget))
        .pipe(uglify())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(destinationFolder));
});

gulp.task('lint', () => {
    return gulp.src(lintSources)
        .pipe(jshint())
        .pipe(jshint.reporter('default'))
        .pipe(jshint.reporter('fail'));
});

gulp.task('watch', () => {
    gulp.watch(watchSources, ['default']);
});

gulp.task('karma', (done) => {
    new KarmaServer({
        configFile: karmaConfig,
        singleRun: true
    }, done).start();
});

gulp.task('watch-karma', (done) => {
    new KarmaServer({
        configFile: karmaConfig,
        autoWatch: true
    }, done).start();
});

gulp.task('phpunit', () => {
    return gulp.src(phpSources)
        .pipe(phpunit('phpunit', {
            configurationFile: phpunitConfig
        }));
});

gulp.task('watch-phpunit', () => {
    gulp.watch(phpSources, ['phpunit']);
});
