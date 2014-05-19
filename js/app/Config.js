/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.config(function ($routeProvider, $provide, $httpProvider) {
    'use strict';

    // constants
    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));

    $provide.constant('FEED_TYPE', {
        FEED: 0,
        FOLDER: 1,
        STARRED: 2,
        SUBSCRIPTIONS: 3,
        SHARED: 4
    });

    // make sure that the CSRF header is only sent to the ownCloud domain
    $provide.factory('CSRFInterceptor', function ($q, BASE_URL) {
        return {
            request: function (config) {
                if (config.url.indexOf(BASE_URL) === 0) {
                    config.headers.requesttoken = oc_requesttoken;
                }

                return config || $q.when(config);
            }
        };
    });
    $httpProvider.interceptors.push('CSRFInterceptor');

    // routing
    $routeProvider
        .when('/items', {
            controller: 'ItemsController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/starred', {
            controller: 'StarredController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/feeds/:id', {
            controller: 'FeedController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/folders/:id', {
            controller: 'FolderController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .otherwise({
            redirectTo: '/items'
        });

});

