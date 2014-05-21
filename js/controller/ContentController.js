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
function (Publisher, FeedResource, ItemResource, data) {
    'use strict';

    // distribute data to models based on key
    Publisher.publishAll(data);

    this.getItems = () => {
        return ItemResource.getAll();
    };

    this.getFeeds = () => {
        return FeedResource.getAll();
    };
});