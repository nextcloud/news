/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(function ($rootScope, $location, $http, $q, $interval, $route, Loading, ItemResource, FeedResource,
                  FolderResource, SettingsResource, Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
    'use strict';

    // show Loading screen
    Loading.setLoading('global', true);

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(ItemResource).toChannels(['items', 'newestItemId',
        'starred', 'unread']);
    Publisher.subscribe(FolderResource).toChannels(['folders']);
    Publisher.subscribe(FeedResource).toChannels(['feeds']);
    Publisher.subscribe(SettingsResource).toChannels(['settings']);

    // load feeds, settings and last read feed
    var settingsPromise = $http.get(BASE_URL + '/settings').then(function (response) {
        Publisher.publishAll(response.data);
        return response.data;
    });

    var path = $location.path();
    var activeFeedPromise = $http.get(BASE_URL + '/feeds/active')
        .then(function (response) {
            var url;
            switch (response.data.activeFeed.type) {
                case FEED_TYPE.FEED:
                    url = '/items/feeds/' + response.data.activeFeed.id;
                    break;

                case FEED_TYPE.FOLDER:
                    url = '/items/folders/' + response.data.activeFeed.id;
                    break;

                case FEED_TYPE.STARRED:
                    url = '/items/starred';
                    break;

                case FEED_TYPE.EXPLORE:
                    url = '/explore';
                    break;

                case FEED_TYPE.UNREAD:
                    url = '/items/unread';
                    break;

                default:
                    url = '/items';
            }

            // only redirect if url is empty or faulty
            if (!/^\/items(\/(starred|unread|explore|feeds\/\d+|folders\/\d+))?\/?$/
                .test(path)) {
                $location.path(url);
            }

            return response.data;
        });

    var feeds;
    var feedPromise = $http.get(BASE_URL + '/feeds').then(function (response) {
        feeds = response.data;
        return feeds;
    });

    var folders;
    var folderPromise = $http.get(BASE_URL + '/folders')
        .then(function (response) {
            folders = response.data;
            return folders;
        });

    $q.all([
        feedPromise,
        folderPromise
    ]).then(function () {
        // first publish feeds to correctly update the folder resource unread
        // cache
        Publisher.publishAll(feeds);
        Publisher.publishAll(folders);
        if (feeds.feeds.length === 0 && folders.folders.length === 0) {
            $location.path('/explore');
        }
    });

    // disable loading if all initial requests finished
    $q.all(
        [
            settingsPromise,
            activeFeedPromise,
            feedPromise,
            folderPromise
        ]
    )
        .then(function () {
            $route.reload();
            Loading.setLoading('global', false);
        });

    // refresh feeds and folders
    $interval(function () {
        $http.get(BASE_URL + '/feeds').then(function (response) {
            Publisher.publishAll(response.data);
        });
        $http.get(BASE_URL + '/folders').then(function (response) {
            Publisher.publishAll(response.data);
        });
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
