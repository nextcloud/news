/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('NavigationController',
function ($route, FEED_TYPE, FeedResource, FolderResource, ItemResource,
    SettingsResource, Publisher, $rootScope) {
    'use strict';

    this.feedError = '';
    this.folderError = '';

    this.getFeeds = function () {
        return FeedResource.getAll();
    };

    this.getFolders = function () {
        return FolderResource.getAll();
    };

    this.markFolderRead = function (folderId) {
        FeedResource.markFolderRead(folderId);

        FeedResource.getByFolderId(folderId).forEach(function (feed) {
            ItemResource.markFeedRead(feed.id);
        });
    };

    this.markFeedRead = function (feedId) {
        ItemResource.markFeedRead(feedId);
        FeedResource.markFeedRead(feedId);
    };

    this.markRead = function () {
        ItemResource.markRead();
        FeedResource.markRead();
    };

    this.isShowAll = function () {
        return SettingsResource.get('showAll');
    };

    this.getFeedsOfFolder = function (folderId) {
        return FeedResource.getByFolderId(folderId);
    };

    this.getUnreadCount = function () {
        return FeedResource.getUnreadCount();
    };

    this.getFeedUnreadCount = function (feedId) {
        return FeedResource.getById(feedId).unreadCount;
    };

    this.getFolderUnreadCount= function (folderId) {
        return FeedResource.getFolderUnreadCount(folderId);
    };

    this.getStarredCount = function () {
        return ItemResource.getStarredCount();
    };

    this.toggleFolder = function (folderName) {
        FolderResource.toggleOpen(folderName);
    };

    this.hasFeeds = function (folderId) {
        return FeedResource.getFolderUnreadCount(folderId) !== undefined;
    };

    this.subFeedActive = function (folderId) {
        var type = $route.current.$$route.type;

        if (type === FEED_TYPE.FEED) {
            var feed = FeedResource.getById($route.current.params.id);

            if (feed.folderId === folderId) {
                return true;
            }
        }

        return false;
    };

    this.isSubscriptionsActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.SUBSCRIPTIONS;
    };

    this.isStarredActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.STARRED;
    };

    this.isFolderActive = function (folderId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FOLDER &&
            currentId === folderId;
    };

    this.isFeedActive = function (feedId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FEED &&
            currentId === feedId;
    };

    this.folderNameExists = function (folderName) {
        folderName = folderName || '';
        return FolderResource.get(folderName) !== undefined;
    };

    this.createFeed = function (feed) {
        this.newFolder = false;

        var self = this;

        // we dont need to create a new folder
        if (feed.folder === undefined) {
            FeedResource.create(feed.url, feed.folderId, undefined)
            .then(function (data) {
                Publisher.publishAll(data);
            });
        } else {
            // create folder first and then the feed
            FolderResource.create(feed.folder).then(function (data) {
                Publisher.publishAll(data);

                self.createFeed({
                    url: feed.url,
                    folderId: data.name
                });

                feed.folderId = data.name;
                feed.folder = '';
            });
        }

        feed.url = '';
    };

    this.createFolder = function (folder) {
        FolderResource.create(folder.name).then(function (data) {
            Publisher.publishAll(data);
        });
        folder.name = '';
    };

    this.moveFeed = function (feedId, folderId) {
        FeedResource.move(feedId, folderId);
    };

    // TBD
    this.renameFeed = function (feed) {
        feed.editing = false;
        // todo remote stuff
    };

    this.renameFolder = function (folder) {
        console.log(folder);
    };

    this.deleteFeed = function (feed) {
        feed.deleted = true;
        // todo remote stuff
    };

    this.undeleteFeed = function (feed) {
        feed.deleted = false;
        // todo remote stuff
    };

    this.removeFeed = function (feed) {
        console.log('remove ' + feed);
    };

    this.deleteFolder = function (folderName) {
        console.log(folderName);
    };

    this.removeFolder = function (folder) {
        console.log('remove ' + folder);
    };

    var self = this;
    $rootScope.$on('moveFeedToFolder', function (scope, data) {
        self.moveFeed(data.feedId, data.folderId);
    });

});