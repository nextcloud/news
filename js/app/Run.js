/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(($rootScope, $location, $http, $q, $interval, Loading, ItemResource,
         FeedResource, FolderResource, Settings, Publisher, BASE_URL, FEED_TYPE,
         REFRESH_RATE) => {
    'use strict';

    // show Loading screen
    Loading.setLoading('global', true);

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(ItemResource).toChannels('items', 'newestItemId',
                                                 'starred');
    Publisher.subscribe(FolderResource).toChannels('folders');
    Publisher.subscribe(FeedResource).toChannels('feeds');
    Publisher.subscribe(Settings).toChannels('settings');

    // load feeds, settings and last read feed
    let settingsDeferred = $q.defer();
    $http.get(`${BASE_URL}/settings`).success((data) => {
        Publisher.publishAll(data);
        settingsDeferred.resolve();
    });

    let activeFeedDeferred = $q.defer();
    $http.get(`${BASE_URL}/feeds/active`).success((data) => {
        let url;

        switch (data.type) {

        case FEED_TYPE.FEED:
            url = '/items/feeds/${data.id}';
            break;

        case FEED_TYPE.FOLDER:
            url = '/items/folders/${data.id}';
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

    let folderDeferred = $q.defer();
    $http.get(`${BASE_URL}/folders`).success((data) => {
        Publisher.publishAll(data);
        folderDeferred.resolve();
    });

    let feedDeferred = $q.defer();
    $http.get(`${BASE_URL}/feeds`).success((data) => {
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
        .then(() => {
            Loading.setLoading('global', false);
        });

    // refresh feeds and folders
    $interval(() => {
        $http.get(`${BASE_URL}/feeds`);
        $http.get(`${BASE_URL}/folders`);
    }, REFRESH_RATE * 1000);


    $rootScope.$on('$routeChangeStart', () => {
        Loading.setLoading('content', true);
    });

    $rootScope.$on('$routeChangeSuccess', () => {
        Loading.setLoading('content', false);
    });

    // in case of wrong id etc show all items
    $rootScope.$on('$routeChangeError', () => {
        $location.path('/items');
    });
});