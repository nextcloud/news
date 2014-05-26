/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('ContentController',
function ($scope, Publisher, FeedResource, ItemResource, SettingsResource,
          data) {
    'use strict';

    $scope.Content = this;

    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);

    this.getItems = () => {
        return ItemResource.getAll();
    };

    // TBD
    this.toggleStar = (itemId) => {
        console.log(itemId);
    };

    this.markRead = (itemId) => {
        console.log(itemId);
    };

    this.getFeed = (feedId) => {
        console.log(feedId);
    };

    this.keepUnread = (itemId) => {
        console.log(itemId);
    };

    this.isContentView = () => {
        console.log('tbd');
    };

    this.orderBy = () => {
        if (SettingsResource.get('oldestFirst')) {
            return '-id';
        } else {
            return 'id';
        }
    };

    this.getRelativeDate = (timestamp) => {
        console.log(timestamp);
    };
});