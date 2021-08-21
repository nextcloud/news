/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

$('#content.app-news')
    .attr('ng-app', 'News')
    .attr('ng-cloak', '')
    .attr('ng-strict-di', '')
    .attr('ng-controller', 'AppController as App');

/* jshint unused: false */
var app = angular.module('News', ['ngRoute', 'ngSanitize', 'ngAnimate']);
