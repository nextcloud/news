/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('AppController', function (Loading, FeedResource, FolderResource) {
    'use strict';

    this.loading = Loading;

    this.isFirstRun = function () {
        return FeedResource.size() === 0 && FolderResource.size() === 0;
    };

    this.play = function (item) {
        this.playingItem = item;
    };
});
