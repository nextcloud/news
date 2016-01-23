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

    var feedType = {
        FEED: 0,
        FOLDER: 1,
        STARRED: 2,
        SUBSCRIPTIONS: 3,
        SHARED: 4,
        EXPLORE: 5
    };

    // constants
    $provide.constant('REFRESH_RATE', 60);  // seconds
    $provide.constant('ITEM_BATCH_SIZE', 40);  // how many items should be
                                               // fetched initially
    $provide.constant('ITEM_AUTO_PAGE_SIZE', 20);
    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
    $provide.constant('FEED_TYPE', feedType);
    $provide.constant('MARK_READ_TIMEOUT', 0.5);
    $provide.constant('SCROLL_TIMEOUT', 0.1);

    // make sure that the CSRF header is only sent to the ownCloud domain
    $provide.factory('CSRFInterceptor', function ($q, BASE_URL, $window) {
        return {
            request: function (config) {
                var domain =
                    $window.location.href.split($window.location.pathname)[0];
                if (config.url.indexOf(BASE_URL) === 0 ||
                    config.url.indexOf(domain) === 0) {
                    config.headers.requesttoken = csrfToken;
                }

                return config || $q.when(config);
            }
        };
    });
    var errorMessages = {
        0: t('news', 'Request failed, network connection unavailable!'),
        401: t('news', 'Request unauthorized. Are you logged in?'),
        403: t('news', 'Request forbidden. Are you an admin?'),
        412: t('news', 'Token expired or app not enabled! Reload the page!'),
        500: t('news', 'Internal server error! Please check your ' +
                       'data/owncloud.log file for additional ' +
                       'information!'),
        503: t('news', 'Request failed, ownCloud is in currently ' +
                       'in maintenance mode!'),
    };
    $provide.factory('ConnectionErrorInterceptor', function ($q, $timeout) {
        var timer;
        return {
            responseError: function (response) {
                // status 0 is a network error
                if (response.status in errorMessages) {
                    if (timer) {
                        $timeout.cancel(timer);
                    }
                    OC.Notification.hide();
                    OC.Notification.showHtml(errorMessages[response.status]);
                    timer = $timeout(function () {
                        OC.Notification.hide();
                    }, 5000);
                }
                return $q.reject(response);
            }
        };
    });
    $httpProvider.interceptors.push('CSRFInterceptor');
    $httpProvider.interceptors.push('ConnectionErrorInterceptor');

    // routing
    var getItemResolve = function (type) {
        return {
            // request to items also returns feeds
            data: /* @ngInject */ function (
            $http, $route, $q, $location, BASE_URL, ITEM_BATCH_SIZE, FEED_TYPE,
            SettingsResource, FeedResource) {

                var showAll = SettingsResource.get('showAll');
                var oldestFirst = SettingsResource.get('oldestFirst');
                var search = $location.search().search || '';

                var deferred = $q.defer();

                // if those two values are null it means we did not receive
                // the settings request from the server so dont query the server
                if (showAll === null || oldestFirst === null) {
                    deferred.resolve({});
                } else {
                    var parameters = {
                        type: type,
                        limit: ITEM_BATCH_SIZE,
                        showAll: showAll,
                        oldestFirst: oldestFirst,
                        search: search
                    };

                    if ($route.current.params.id !== undefined) {
                        parameters.id = $route.current.params.id;
                    }

                    // check if a custom ordering is set
                    if (type === FEED_TYPE.FEED) {
                        var feed = FeedResource.getById(parameters.id);

                        // on intial load, the feed ordering is undefined
                        if (feed === undefined || feed.ordering === 2) {
                            parameters.oldestFirst = false;
                        } else if (feed.ordering === 1) {
                            parameters.oldestFirst = true;
                        }
                    }

                    $http({
                        url:  BASE_URL + '/items',
                        method: 'GET',
                        params: parameters
                    }).success(function (data) {
                        deferred.resolve(data);
                    });
                }

                return deferred.promise;
            }
        };
    };

    var getExploreResolve = function () {
        return {
            sites: /* @ngInject */ function (
            $http, $q, BASE_URL, $location, Publisher, SettingsResource) {
                var deferred = $q.defer();

                // always use the code from the url
                var language = $location.search().lang;
                if (!language) {
                    language = SettingsResource.get('language');
                }

                $http.get(BASE_URL + '/settings').then(function (data) {
                    Publisher.publishAll(data);

                    // get url and strip trailing slashes
                    var url = SettingsResource.get('exploreUrl')
                        .replace(/\/+$/, '');

                    var exploreUrl = url + '/feeds.' + language + '.json';
                    var defaultExploreUrl = url + '/feeds.en.json';
                    return $http
                        .get(exploreUrl)
                        .catch(function () {
                            return $http.get(defaultExploreUrl);
                        });

                }).then(function (data) {
                    deferred.resolve(data.data);
                }).catch(function () {
                    deferred.reject();
                });

                return deferred.promise;
            }
        };
    };

    $routeProvider
        .when('/items', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getItemResolve(feedType.SUBSCRIPTIONS),
            type: feedType.SUBSCRIPTIONS
        })
        .when('/items/starred', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getItemResolve(feedType.STARRED),
            type: feedType.STARRED
        })
        .when('/items/feeds/:id', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getItemResolve(feedType.FEED),
            type: feedType.FEED
        })
        .when('/items/folders/:id', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getItemResolve(feedType.FOLDER),
            type: feedType.FOLDER
        }).when('/explore', {
            controller: 'ExploreController as Explore',
            templateUrl: 'explore.html',
            resolve: getExploreResolve(),
            type: feedType.EXPLORE
        }).when('/shortcuts', {
            templateUrl: 'shortcuts.html',
            type: -1
        });

});
