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
function (Publisher, FeedResource, ItemResource, SettingsResource, data,
    $route, $routeParams, FEED_TYPE) {
    'use strict';

    // dont cache items across multiple route changes
    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);


    this.isAutoPagingEnabled = true;

    this.getItems = function () {
        return ItemResource.getAll();
    };

    this.toggleStar = function (itemId) {
        ItemResource.toggleStar(itemId);
    };

    this.toggleItem = function (item) {
        // TODO: unittest
        if (this.isCompactView()) {
            item.show = !item.show;
        }
    };

    this.markRead = function (itemId) {
        var item = ItemResource.get(itemId);

        if (!item.keepUnread && item.unread === true) {
            ItemResource.markItemRead(itemId);
            FeedResource.markItemOfFeedRead(item.feedId);
        }
    };

    this.getFeed = function (feedId) {
        return FeedResource.getById(feedId);
    };

    this.toggleKeepUnread = function (itemId) {
        var item = ItemResource.get(itemId);
        if (!item.unread) {
            FeedResource.markItemOfFeedUnread(item.feedId);
            ItemResource.markItemRead(itemId, false);
        }

        item.keepUnread = !item.keepUnread;
    };

    this.orderBy = function () {
        if (SettingsResource.get('oldestFirst')) {
            return '-id';
        } else {
            return 'id';
        }
    };

    this.isCompactView = function () {
        return SettingsResource.get('compact');
    };

    this.autoPagingEnabled = function () {
        return this.isAutoPagingEnabled;
    };

    this.markReadEnabled = function () {
        return !SettingsResource.get('preventReadOnScroll');
    };

    this.scrollRead = function (itemIds) {
        var ids = [];
        var feedIds = [];

        itemIds.forEach(function (itemId) {
            var item = ItemResource.get(itemId);
            if (!item.keepUnread) {
                ids.push(itemId);
                feedIds.push(item.feedId);
            }
        });

        FeedResource.markItemsOfFeedsRead(feedIds);
        ItemResource.markItemsRead(ids);
    };

    this.isFeed = function () {
        return $route.current.$$route.type === FEED_TYPE.FEED;
    };

    this.autoPage = function () {
        this.isAutoPagingEnabled = false;

        var type = $route.current.$$route.type;
        var id = $routeParams.id;

        var self = this;
        ItemResource.autoPage(type, id).success(function (data) {
            Publisher.publishAll(data);

            if (data.items.length > 0) {
                self.isAutoPagingEnabled = true;
            }
        }).error(function () {
            self.isAutoPagingEnabled = true;
        });
    };

    this.getRelativeDate = function (timestamp) {
        if (timestamp !== undefined && timestamp !== '') {
            var languageCode = SettingsResource.get('language');
            var date =
                moment.unix(timestamp).locale(languageCode).fromNow() + '';
            return date;
        } else {
            return '';
        }
    };

});