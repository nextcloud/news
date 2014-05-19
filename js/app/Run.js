/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(function ($rootScope, $location, $http, $q, $interval, Loading, Item,
                  Feed, Folder, Settings, Publisher, BASE_URL, FEED_TYPE,
                  CONFIG) {
    'use strict';

    // show Loading screen
    Loading.setLoading('global', true);

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(Item).toChannel('items');
    Publisher.subscribe(Folder).toChannel('folders');
    Publisher.subscribe(Feed).toChannel('feeds');
    Publisher.subscribe(Settings).toChannel('settings');

    // load feeds, settings and last read feed
    var settingsDeferred,
        activeFeedDeferred;

    settingsDeferred = $q.defer();
    $http.get(BASE_URL + '/settings').then(function (data) {
        Publisher.publishAll(data);
        settingsDeferred.resolve();
    });

    activeFeedDeferred = $q.defer();
    $http.get(BASE_URL + '/feeds/active').then(function (data) {
        var url;

        switch (data.type) {

        case FEED_TYPE.FEED:
            url = '/items/feeds/' + data.id;
            break;

        case FEED_TYPE.FOLDER:
            url = '/items/folders/' + data.id;
            break;

        case FEED_TYPE.STARRED:
            url = '/items/starred';
            break;

        default:
            url = '/items';
        }

        $location.path(url);
        activeFeedDeferred.resolve();
    });

    // disable loading if all initial requests finished
    $q.all([settingsDeferred.promise, activeFeedDeferred.promise])
        .then(function () {
            Loading.setLoading('global', false);
        });

    // refresh feeds and folders
    $interval(function () {
        $http.get(BASE_URL + '/feeds');
        $http.get(BASE_URL + '/folders');
    }, CONFIG.REFRESH_RATE * 1000);


    $rootScope.$on('$routeChangeStart', function () {
        Loading.setLoading('content', true);
    });

    $rootScope.$on('$routeChangeSuccess', function () {
        Loading.setLoading('content', false);
    });

    // in case of wrong id etc show all items
    $rootScope.$on('$routeChangeError', function () {
        $location.path('/items');
    });
});