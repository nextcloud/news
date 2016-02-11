/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

'use strict';

let gulp = require('gulp'),
    ngAnnotate = require('gulp-ng-annotate'),
    uglify = require('gulp-uglify'),
    jshint = require('gulp-jshint'),
    KarmaServer = require('karma').Server,
    phpunit = require('gulp-phpunit'),
    concat = require('gulp-concat'),
    sourcemaps = require('gulp-sourcemaps');

/**
 * Configuration
 */
let phpunitConfig = __dirname + '/../phpunit.xml';
let karmaConfig = __dirname + '/karma.conf.js';
let destinationFolder = __dirname + '/build/';
let sources = [
    'app/App.js', 'app/Config.js', 'app/Run.js',
    'controller/**/*.js',
    'filter/**/*.js',
    'service/**/*.js',
    'gui/**/*.js',
    'plugin/**/*.js',
    'utility/**/*.js',
    'directive/**/*.js'
];

let testSources = [
    'tests/**/*.js'
];

let phpSources = [
    '../*/**.php',
    '!../js/*/**',
    '!../vendor/*/**'
];

gulp.task('default', ['lint'], () => {
    return gulp.src(sources)
        .pipe(ngAnnotate())
        .pipe(sourcemaps.init())
        .pipe(concat('app.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(destinationFolder));
});

gulp.task('lint', () => {
    return gulp.src('*/**.js')
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(jshint.reporter('fail'));
});

gulp.task('watch', () => {
    gulp.watch(sources.concat(testSources).concat('*.js'), ['default']);
});

gulp.task('karma', (done) => {
    new KarmaServer({
        configFile: karmaConfig,
        singleRun: true
    }, done).start();
})

gulp.task('watch-karma', (done) => {
    new KarmaServer({
        configFile: karmaConfig,
        autoWatch: true
    }, done).start();
})

gulp.task('phpunit', () => {
    gulp.src(phpSources)
        .pipe(phpunit('phpunit', {
            configurationFile: phpunitConfig
        }));
});

gulp.task('watch-phpunit', () => {
    gulp.watch(phpSources, ['phpunit']);
});
