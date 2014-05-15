/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.config(function ($routeProvider, $provide) {
    'use strict';

    $provide.constant('baseUrl', OC.generateUrl(''));

    $routeProvider
        .when('/items', {
            controller: 'AllItemsController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/starred', {
            controller: 'StarredItemsController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/feeds/:id', {
            controller: 'FeedItemsController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .when('/items/folders/:id', {
            controller: 'FolderItemsController',
            templateUrl: 'content.html',
            resolve: {}
        })
        .otherwise({
            redirectTo: '/items'
        });

});

