/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('ContentController', function (Publisher, FeedResource, ItemResource, SettingsResource, data, $route,
                                              $routeParams, $location, FEED_TYPE, ITEM_AUTO_PAGE_SIZE, Loading) {
    'use strict';

    this.showDropdown = [];
    var self = this;
    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);

    var getOrdering = function () {
        var ordering = SettingsResource.get('oldestFirst');

        if (self.isFeed()) {
            var feed = FeedResource.getById($routeParams.id);
            if (feed && feed.ordering === 1) {
                ordering = true;
            } else if (feed && feed.ordering === 2) {
                ordering = false;
            }
        }

        return ordering;
    };

    this.getFirstItem = function () {
        var orderedItems = this.getItems();
        var item = orderedItems[orderedItems.length - 1];
        var firstItem = orderedItems[0];
        // If getOrdering == 1, then the sorting is set to
        // newest first. So, item should be the first item
        //
        if (getOrdering()) {
            item = firstItem;
        }
        if (item === undefined) {
            return undefined;
        }
        else {
            return item.id;
        }
    };


    this.isAutoPagingEnabled = true;
    // the interface should show a hint if there are not enough items sent
    // it's assumed that theres nothing to autpage

    this.isNothingMoreToAutoPage = ItemResource.size() < ITEM_AUTO_PAGE_SIZE;

    this.getItems = function () {
        return ItemResource.getAll();
    };

    this.isItemActive = function (id) {
        return this.activeItem === id;
    };

    this.setItemActive = function (id) {
        this.activeItem = id;
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

    this.sortIds = function(first, second) {
        var firstInt = parseInt(first.value);
        var secondInt = parseInt(second.value);
        return (firstInt < secondInt) ? 1 : -1;
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

    this.oldestFirst = getOrdering();

    this.autoPage = function () {
        if (this.isNothingMoreToAutoPage) {
            return;
        }

        // in case a subsequent autopage request comes in wait until
        // the current one finished and execute a request immediately
        // afterwards
        if (!this.isAutoPagingEnabled) {
            this.autoPageAgain = true;
            return;
        }

        this.isAutoPagingEnabled = false;
        this.autoPageAgain = false;

        var type = $route.current.$$route.type;
        var id = $routeParams.id;
        var showAll = SettingsResource.get('showAll');
        var self = this;
        var search = $location.search().search;

        Loading.setLoading('autopaging', true);

        ItemResource.autoPage(type, id, this.oldestFirst, showAll, search).then(function (response) {
            Publisher.publishAll(response.data);

            if (response.data.items.length >= ITEM_AUTO_PAGE_SIZE) {
                self.isAutoPagingEnabled = true;
            } else {
                self.isNothingMoreToAutoPage = true;
            }

            if (self.isAutoPagingEnabled && self.autoPageAgain) {
                self.autoPage();
            }
            return response.data;
        }, function () {
            self.isAutoPagingEnabled = true;
        }).finally(function () {
            Loading.setLoading('autopaging', false);
        });
    };

    this.refresh = function () {
        $route.reload();
    };

    this.getMediaType = function (type) {
        if (type && type.indexOf('audio') === 0) {
            return 'audio';
        } else if (type && type.indexOf('video') === 0) {
            return 'video';
        } else {
            return undefined;
        }
    };

    this.activeItem = this.getFirstItem();

    this.openDropdown = function(itemId){
        let actualItem = this.showDropdown[itemId];
        this.showDropdown = [];
        this.showDropdown[itemId] = !actualItem;
    };

    this.hide = function(){
        this.showDropdown = [];
    };

});
