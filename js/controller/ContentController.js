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
    $route, $routeParams, FEED_TYPE, ITEM_AUTO_PAGE_SIZE, Loading) {
    'use strict';

    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);


    this.isAutoPagingEnabled = true;

    // the interface should show a hint if there are not enough items sent so
    // it's assumed that theres nothing to autpage
    if (ItemResource.size() >= ITEM_AUTO_PAGE_SIZE) {
        this.isNothingMoreToAutoPage = false;
    } else {
        this.isNothingMoreToAutoPage = true;
    }

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

    this.isShowAll = function () {
        return SettingsResource.get('showAll');
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
            return 'id';
        } else {
            return '-id';
        }
    };

    this.isCompactView = function () {
        return SettingsResource.get('compact');
    };

    this.isCompactExpand = function () {
        return SettingsResource.get('compactExpand');
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

        if (ids.length > 0) {
            FeedResource.markItemsOfFeedsRead(feedIds);
            ItemResource.markItemsRead(ids);
        }
    };

    this.isFeed = function () {
        return $route.current.$$route.type === FEED_TYPE.FEED;
    };

    this.autoPage = function () {
        if (this.isNothingMoreToAutoPage) {
            return;
        }

        // in case a subsequent autopage request comes in wait until
        // the current one finished and execute a request immediately afterwards
        if (!this.isAutoPagingEnabled) {
            this.autoPageAgain = true;
            return;
        }

        this.isAutoPagingEnabled = false;
        this.autoPageAgain = false;

        var type = $route.current.$$route.type;
        var id = $routeParams.id;
        var oldestFirst = SettingsResource.get('oldestFirst');
        var showAll = SettingsResource.get('showAll');
        var self = this;

        Loading.setLoading('autopaging', true);

        ItemResource.autoPage(type, id, oldestFirst, showAll)
        .success(function (data) {
            Publisher.publishAll(data);

            if (data.items.length >= ITEM_AUTO_PAGE_SIZE) {
                self.isAutoPagingEnabled = true;
            } else {
                self.isNothingMoreToAutoPage = true;
            }

            if (self.isAutoPagingEnabled && self.autoPageAgain) {
                self.autoPage();
            }
        }).error(function () {
            self.isAutoPagingEnabled = true;
        }).finally(function () {
            Loading.setLoading('autopaging', false);
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

    this.refresh = function () {
        $route.reload();
    };

});