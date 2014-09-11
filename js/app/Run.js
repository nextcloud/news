/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(function ($rootScope, $location, $http, $q, $interval, Loading,
         ItemResource, FeedResource, FolderResource, SettingsResource,
          Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
    'use strict';

    // show Loading screen
    Loading.setLoading('global', true);

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(ItemResource).toChannels(['items', 'newestItemId',
                                                  'starred']);
    Publisher.subscribe(FolderResource).toChannels(['folders']);
    Publisher.subscribe(FeedResource).toChannels(['feeds']);
    Publisher.subscribe(SettingsResource).toChannels(['settings']);

    // load feeds, settings and last read feed
    var settingsDeferred = $q.defer();
    $http.get(BASE_URL + '/settings').success(function (data) {
        Publisher.publishAll(data);
        settingsDeferred.resolve();
    });

    var activeFeedDeferred = $q.defer();
    var path = $location.path();
    $http.get(BASE_URL + '/feeds/active').success(function (data) {
        var url;

        switch (data.activeFeed.type) {

        case FEED_TYPE.FEED:
            url = '/items/feeds/' + data.activeFeed.id;
            break;

        case FEED_TYPE.FOLDER:
            url = '/items/folders/' + data.activeFeed.id;
            break;

        case FEED_TYPE.STARRED:
            url = '/items/starred';
            break;

        default:
            url = '/items';
        }

        // only redirect if url is empty or faulty
        if (!/^\/items(\/(starred|feeds\/\d+|folders\/\d+))?\/?$/.test(path)) {
            $location.path(url);
        }

        activeFeedDeferred.resolve();
    });

    var folderDeferred = $q.defer();
    $http.get(BASE_URL + '/folders').success(function (data) {
        Publisher.publishAll(data);
        folderDeferred.resolve();
    });

    var feedDeferred = $q.defer();
    $http.get(BASE_URL + '/feeds').success(function (data) {
        Publisher.publishAll(data);
        feedDeferred.resolve();
    });

    // disable loading if all initial requests finished
    $q.all(
        [
            settingsDeferred.promise,
            activeFeedDeferred.promise,
            feedDeferred.promise,
            folderDeferred.promise
        ]
    )
        .then(function () {
            Loading.setLoading('global', false);
        });

    // refresh feeds and folders
    $interval(function () {
        $http.get(BASE_URL + '/feeds');
        $http.get(BASE_URL + '/folders');
    }, REFRESH_RATE * 1000);


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