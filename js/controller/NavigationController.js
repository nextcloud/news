/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('NavigationController', function ($route, FEED_TYPE, FeedResource, FolderResource, ItemResource,
                                                 SettingsResource, Publisher, $rootScope, $location, $q) {
    'use strict';

    this.feedError = '';
    this.showNewFolder = false;
    this.renamingFolder = false;
    this.addingFeed = false;
    this.addingFolder = false;
    this.folderError = '';
    this.renameError = '';
    this.feed = {};
    this.youtubeDetectorRegex = new RegExp(/youtube\.[a-z\.]{2,}\/(user|channel)\/(.*?)(\/|\?|$)/);

    var getRouteId = function () {
        return parseInt($route.current.params.id, 10);
    };

    this.getLanguageCode = function () {
        return SettingsResource.get('language');
    };

    this.getFeeds = function () {
        return FeedResource.getAll();
    };

    this.getFolders = function () {
        return FolderResource.getAll();
    };

    this.markCurrentRead = function () {
      var id = getRouteId();
      var type = $route.current.$$route.type;

      if(isNaN(id)) {
        this.markRead();
      } else if(type === FEED_TYPE.FOLDER) {
        this.markFolderRead(id);
      } else if(type === FEED_TYPE.FEED) {
        this.markFeedRead(id);
      }
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

    this.isUnread = function () {
        return this.getUnreadCount() > 0;
    };

    this.getFeedUnreadCount = function (feedId) {
        var feed = FeedResource.getById(feedId);
        if (feed !== undefined) {
            return feed.unreadCount;
        } else {
            return 0;
        }
    };

    this.isFeedUnread = function (feedId) {
        return this.getFeedUnreadCount(feedId) > 0;
    };

    this.getFolderUnreadCount = function (folderId) {
        return FeedResource.getFolderUnreadCount(folderId);
    };

    this.isFolderUnread = function (folderId) {
        return this.getFolderUnreadCount(folderId) > 0;
    };

    this.getStarredCount = function () {
        return ItemResource.getStarredCount();
    };

    this.isStarredUnread = function () {
        return this.getStarredCount() > 0;
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
            var feed = FeedResource.getById(getRouteId());

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

    this.isUnreadActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.UNREAD;
    };

    this.isStarredActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.STARRED;
    };

    this.isExploreActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.EXPLORE;
    };

    this.isFolderActive = function (folderId) {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FOLDER &&
            getRouteId() === folderId;
    };

    this.isFeedActive = function (feedId) {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FEED &&
            getRouteId() === feedId;
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
        this.showNewFolder = false;
        this.addingFeed = true;

        var newFolder = feed.newFolder;
        var existingFolder = feed.existingFolder || {id: 0};

        // we dont need to create a new folder
        if (newFolder === undefined || newFolder === '') {
            // this is set to display the feed in any folder, even if the folder
            // is closed or has no unread articles
            existingFolder.getsFeed = true;

            /**
             * Transform youtube channel and user URL into their RSS feed
             * (09/01/2020): Youtube feed url work as `https://www.youtube.com/feeds/videos.xml?user=<username>`
             */
            var regResult = this.youtubeDetectorRegex.exec(feed.url);
            /**
             * At this point:
             * regResult[0] contain the match
             * regResult[1] contain the type of youtube entity (channel or user)
             * regResult[2] contain either the username or the channel id
             */
            if (regResult && regResult[0] && regResult[1] && regResult[2]) {
                feed.url = 'https://www.youtube.com/feeds/videos.xml?';
                feed.url += (regResult[1] === 'user') ? 'user=' : 'channel_id=';
                feed.url += regResult[2];
            }

            var autoDiscover = feed.autoDiscover ? true : false;
            FeedResource.create(feed.url, existingFolder.id, undefined, feed.user, feed.password, autoDiscover)
            .then(function (data) {
                Publisher.publishAll(data);

                // set folder as default
                $location.path('/items/feeds/' + data.feeds[0].id + '/');
            }).finally(function () {
                existingFolder.getsFeed = undefined;
                feed.url = '';
                feed.user = '';
                feed.password = '';
                feed.autoDiscover = true;
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
        FeedResource.patch(feed.id, {title: feed.title});
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

    this.setOrdering = function (feed, ordering) {
        FeedResource.patch(feed.id, {ordering: ordering});
        $route.reload();
    };

    this.togglePinned = function (feedId) {
        var feed = FeedResource.getById(feedId);
        if (feed) {
            return FeedResource.patch(feedId, {pinned: !feed.pinned});
        }
    };

    this.setUpdateMode = function (feedId, updateMode) {
        return FeedResource.patch(feedId, {updateMode: updateMode});
    };

    this.toggleFullText = function (feed) {
        $rootScope.$broadcast('$routeChangeStart');
        FeedResource.toggleFullText(feed.id).finally(function () {
            $rootScope.$broadcast('$routeChangeSuccess');
            $route.reload();
        });
    };

    this.search = function (value) {
        if (value === '') {
            $location.search('search', null);
        } else {
            $location.search('search', value);
        }
    };

    var self = this;

    $rootScope.$on('moveFeedToFolder', function (scope, data) {
        self.moveFeed(data.feedId, data.folderId);
    });

    // based on the route we want to preselect a folder in the add new feed
    // drop down
    var setSelectedFolderForRoute = function () {
        var type;
        if ($route.current) {
            type = $route.current.$$route.type;
        }

        var folderId = 0;

        if (type === FEED_TYPE.FOLDER) {
            folderId = getRouteId();
        } else if (type === FEED_TYPE.FEED) {
            var feed = FeedResource.getById(getRouteId());

            if (feed) {
                folderId = feed.folderId;
            }
        }

        var folder;
        if (folderId !== 0) {
            folder = FolderResource.getById(folderId);
        }

        self.feed.existingFolder = folder;
    };

    $rootScope.$on('$routeChangeSuccess', function () {
        setSelectedFolderForRoute();
    });

    $rootScope.localeComparator = function(v1, v2) {
        if (v1.type === 'string' && v2.type === 'string') {
            return v1.value.localeCompare(v2.value);
        }

        return (v1.value === v2.value) ? 0 : ((v1.value < v2.value) ? -1 : 1);
    };
});
