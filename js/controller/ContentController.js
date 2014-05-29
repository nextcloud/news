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
function (Publisher, FeedResource, ItemResource, SettingsResource, data) {
    'use strict';

    this.isAutoPagingEnabled = true;

    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);

    this.getItems = () => {
        return ItemResource.getAll();
    };

    this.toggleStar = (itemId) => {
        ItemResource.toggleStar(itemId);
    };

    this.markRead = (itemId) => {
        ItemResource.markItemRead(itemId);

        let item = ItemResource.get(itemId);
        FeedResource.markItemOfFeedRead(item.feedId);
    };

    this.getFeed = (feedId) => {
        return FeedResource.getById(feedId);
    };

    this.toggleKeepUnread = (itemId) => {
        let item = ItemResource.get(itemId);
        if (!item.unread) {
            FeedResource.markItemOfFeedUnread(item.feedId);
            ItemResource.markItemRead(itemId, false);
        }

        item.keepUnread = !item.keepUnread;
    };

    this.orderBy = () => {
        if (SettingsResource.get('oldestFirst')) {
            return '-id';
        } else {
            return 'id';
        }
    };

    this.isCompactView = () => {
        return SettingsResource.get('compact');
    };

    // TBD
    this.getRelativeDate = (timestamp) => {
        console.log(timestamp);
    };

    this.autoPage = () => {
        console.log('hi');
    };

    this.scrollRead = (itemIds) => {
        console.log(itemIds);
    };

    this.autoPagingEnabled = () => {
        return this.isAutoPagingEnabled;
    };

    this.markReadEnabled = () => {
        return !SettingsResource.get('preventReadOnScroll');
    };

});