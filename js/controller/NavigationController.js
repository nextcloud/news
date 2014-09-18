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
    SettingsResource, Publisher, $rootScope, $location, $q) {
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
        var feed = FeedResource.getById(feedId);
        if (feed !== undefined) {
            return feed.unreadCount;
        } else {
            return 0;
        }
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

            if (feed !== undefined && feed.folderId === folderId) {
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
        return FolderResource.get(folderName.trim()) !== undefined;
    };

    this.feedUrlExists = function (url) {
        url = url || '';
        url = url.trim();
        return FeedResource.get(url) !== undefined ||
            FeedResource.get('http://' + url) !== undefined;
    };

    this.createFeed = function (feed) {
        var self = this;
        this.newFolder = false;
        this.addingFeed = true;

        var newFolder = feed.newFolder;
        var existingFolder = feed.existingFolder || {id: 0};

        // we dont need to create a new folder
        if (newFolder === undefined) {
            // this is set to display the feed in any folder, even if the folder
            // is closed or has no unread articles
            existingFolder.getsFeed = true;

            FeedResource.create(feed.url, existingFolder.id, undefined)
            .then(function (data) {

                Publisher.publishAll(data);

                // set folder as default
                $location.path('/items/feeds/' + data.feeds[0].id + '/');

            }).finally(function () {
                existingFolder.getsFeed = undefined;
                feed.url = '';
                self.addingFeed = false;
            });

        } else {
            // create folder first and then the feed
            FolderResource.create(newFolder).then(function (data) {

                Publisher.publishAll(data);

                // set the created folder on scope so its preselected for the
                // next addition
                feed.existingFolder = FolderResource.get(data.folders[0].name);
                feed.newFolder = undefined;
                self.createFeed(feed);
            });
        }
    };

    this.createFolder = function (folder) {
        var self = this;
        this.addingFolder = true;
        FolderResource.create(folder.name).then(function (data) {
            Publisher.publishAll(data);
        }).finally(function () {
            self.addingFolder = false;
            folder.name = '';
        });
    };

    this.moveFeed = function (feedId, folderId) {
        var reload = false;
        var feed = FeedResource.getById(feedId);

        if (feed.folderId === folderId) {
            return;
        }

        if (this.isFolderActive(feed.folderId) ||
            this.isFolderActive(folderId)) {
            reload = true;
        }

        FeedResource.move(feedId, folderId);

        if (reload) {
            $route.reload();
        }
    };

    this.renameFeed = function (feed) {
        FeedResource.rename(feed.id, feed.title);
        feed.editing = false;
    };

    this.renameFolder = function (folder, name) {
        folder.renameError = '';
        this.renamingFolder = true;
        var self = this;

        if (folder.name === name) {
            folder.renameError = '';
            folder.editing = false;
            this.renamingFolder = false;
        } else {
            FolderResource.rename(folder.name, name).then(function () {
                folder.renameError = '';
                folder.editing = false;
            }, function (message) {
                folder.renameError = message;
            }).finally(function () {
                self.renamingFolder = false;
            });
        }
    };

    this.reversiblyDeleteFeed = function (feed) {
        FeedResource.reversiblyDelete(feed.id).finally(function () {
            $route.reload();
        });
    };

    this.undoDeleteFeed = function (feed) {
        FeedResource.undoDelete(feed.id).finally(function () {
            $route.reload();
        });
    };

    this.deleteFeed = function (feed) {
        console.log('deleted!');
        FeedResource.delete(feed.url);
    };


    this.reversiblyDeleteFolder = function (folder) {
        $q.all(
            FeedResource.reversiblyDeleteFolder(folder.id),
            FolderResource.reversiblyDelete(folder.name)
        ).finally(function () {
            $route.reload();
        });
    };

    this.undoDeleteFolder = function (folder) {
        $q.all(
            FeedResource.undoDeleteFolder(folder.id),
            FolderResource.undoDelete(folder.name)
        ).finally(function () {
            $route.reload();
        });
    };

    this.deleteFolder = function (folder) {
        FeedResource.deleteFolder(folder.id);
        FolderResource.delete(folder.name);
    };

    var self = this;
    $rootScope.$on('moveFeedToFolder', function (scope, data) {
        self.moveFeed(data.feedId, data.folderId);
    });

});