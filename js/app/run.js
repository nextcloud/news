/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(function ($rootScope, $location, Loading, Setup, Item, Feed, Folder,
                  Publisher, Settings) {
    'use strict';

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(Item).toChannel('items');
    Publisher.subscribe(Folder).toChannel('folders');
    Publisher.subscribe(Feed).toChannel('feeds');
    Publisher.subscribe(Settings).toChannel('settings');

    // load feeds, settings and last read feed
    Setup.load();

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