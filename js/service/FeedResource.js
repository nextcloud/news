/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FeedResource', function (Resource, $http, BASE_URL, $q) {
    'use strict';

    var FeedResource = function ($http, BASE_URL, $q) {
        Resource.call(this, $http, BASE_URL, 'url');
        this.ids = {};
        this.locations = {};
        this.unreadCount = 0;
        this.folderUnreadCount = {};
        this.folderIds = {};
        this.$q = $q;
    };

    FeedResource.prototype = Object.create(Resource.prototype);

    FeedResource.prototype.receive = function (data) {
        Resource.prototype.receive.call(this, data);
        this.updateUnreadCache();
        this.updateFolderCache();
    };

    FeedResource.prototype.clear = function () {
        Resource.prototype.clear.call(this);
        this.unreadCount = 0;
        this.folderUnreadCount = {};
        this.folderIds = {};
        this.ids = {};
        this.locations = {};
    };

    FeedResource.prototype.updateUnreadCache = function () {
        this.unreadCount = 0;
        this.folderUnreadCount = {};

        var self = this;
        this.values.forEach(function (feed) {
            if (feed.unreadCount) {
                self.unreadCount += feed.unreadCount;
            }
            if (feed.folderId !== undefined) {
                self.folderUnreadCount[feed.folderId] =
                    self.folderUnreadCount[feed.folderId] || 0;
                self.folderUnreadCount[feed.folderId] += feed.unreadCount;
            }
        });
    };


    FeedResource.prototype.updateFolderCache = function () {
        this.folderIds = {};

        var self = this;
        this.values.forEach(function (feed) {
            self.folderIds[feed.folderId] =
                self.folderIds[feed.folderId] || [];
            self.folderIds[feed.folderId].push(feed);
        });
    };


    FeedResource.prototype.add = function (value) {
        Resource.prototype.add.call(this, value);
        if (value.id !== undefined) {
            this.ids[value.id] = this.hashMap[value.url];
        }
        if (value.location !== undefined) {
            this.locations[value.location] = this.hashMap[value.url];
        }
    };


    FeedResource.prototype.markRead = function () {
        this.values.forEach(function (feed) {
            feed.unreadCount = 0;
        });

        this.updateUnreadCache();
    };


    FeedResource.prototype.markFeedRead = function (feedId) {
        this.ids[feedId].unreadCount = 0;
        this.updateUnreadCache();
    };


    FeedResource.prototype.markFolderRead = function (folderId) {
        this.values.forEach(function (feed) {
            if (feed.folderId === folderId) {
                feed.unreadCount = 0;
            }
        });

        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemOfFeedRead = function (feedId) {
        this.ids[feedId].unreadCount -= 1;
        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemsOfFeedsRead = function (feedIds) {
        var self = this;
        feedIds.forEach(function (feedId) {
            self.ids[feedId].unreadCount -= 1;
        });

        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemOfFeedUnread = function (feedId) {
        this.ids[feedId].unreadCount += 1;
        this.updateUnreadCache();
    };


    FeedResource.prototype.getUnreadCount = function () {
        return this.unreadCount;
    };


    FeedResource.prototype.getFolderUnreadCount = function (folderId) {
        return this.folderUnreadCount[folderId];
    };


    FeedResource.prototype.getByFolderId = function (folderId) {
        return this.folderIds[folderId] || [];
    };


    FeedResource.prototype.getById = function (feedId) {
        return this.ids[feedId];
    };


    FeedResource.prototype.getByLocation = function (location) {
        return this.locations[location];
    };


    FeedResource.prototype.move = function (feedId, folderId) {
        var feed = this.getById(feedId);
        feed.folderId = folderId;

        this.updateFolderCache();
        this.updateUnreadCache();

        return this.patch(feedId, {folderId: folderId});

    };


    FeedResource.prototype.create = function (url, folderId, title, user, password, fullDiscover) {
        url = url.trim();
        if (!url.startsWith('http')) {
            url = 'https://' + url;
        }

        if (title !== undefined) {
            title = title.trim();
        }

        var feed = {
            url: url,
            folderId: folderId || 0,
            title: title || url,
            unreadCount: 0
        };

        this.add(feed);
        this.updateFolderCache();

        return this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds',
            data: {
                url: url,
                parentFolderId: folderId || 0,
                title: title,
                user: user || null,
                password: password || null,
                fullDiscover: fullDiscover
            }
        }).then(function (response) {
            return response.data;
        }, function (response) {
            feed.faviconLink = '';
            feed.error = response.data.message;
        });
    };


    FeedResource.prototype.reversiblyDelete = function (id, updateCache, isFolder) {
        var feed = this.getById(id);

        // if a folder is deleted it does not have to trigger the delete
        // attribute for the feed because the feed is not deleted, its just not
        // displayed. Otherwise this causes the feed to also be deleted again
        // because the folder destroys the feed's scope
        if (feed && isFolder !== true) {
            feed.deleted = true;
        }

        if (updateCache !== false) {
            this.updateUnreadCache();
        }

        return this.http.delete(this.BASE_URL + '/feeds/' + id);
    };


    FeedResource.prototype.reversiblyDeleteFolder = function (folderId) {
        var self = this;
        var promises = [];
        this.getByFolderId(folderId).forEach(function (feed) {
            promises.push(self.reversiblyDelete(feed.id, false, true));
        });

        this.updateUnreadCache();

        var deferred = this.$q.all(promises);
        return deferred.promise;
    };


    FeedResource.prototype.delete = function (url, updateCache) {
        var feed = this.get(url);
        if (feed !== undefined && feed.id) {
            delete this.ids[feed.id];
        }

        if (feed !== undefined && feed.location) {
            delete this.locations[feed.location];
        }

        Resource.prototype.delete.call(this, url);

        if (updateCache !== false) {
            this.updateUnreadCache();
            this.updateFolderCache();
        }

        return feed;
    };


    FeedResource.prototype.deleteFolder = function (folderId) {
        var self = this;
        this.getByFolderId(folderId).forEach(function (feed) {
            self.delete(feed.url, false);
        });

        this.updateUnreadCache();
        this.updateFolderCache();
    };


    FeedResource.prototype.undoDelete = function (id, updateCache) {
        var feed = this.getById(id);

        if (feed) {
            feed.deleted = false;
        }

        if (updateCache !== false) {
            this.updateUnreadCache();
        }

        return this.http.post(this.BASE_URL + '/feeds/' + id + '/restore');
    };


    FeedResource.prototype.undoDeleteFolder = function (folderId) {
        var self = this;
        var promises = [];

        this.getByFolderId(folderId).forEach(function (feed) {
            promises.push(self.undoDelete(feed.id, false));
        });

        this.updateUnreadCache();

        var deferred = this.$q.all(promises);
        return deferred.promise;
    };


    FeedResource.prototype.setOrdering = function (feedId, ordering) {
        var feed = this.getById(feedId);

        if (feed) {
            feed.ordering = ordering;
            var url = this.BASE_URL + '/feeds/' + feedId;
            return this.http.patch(url, {
                ordering: ordering
            });
        }
    };


    FeedResource.prototype.setPinned = function (feedId, isPinned) {
        var feed = this.getById(feedId);

        if (feed) {
            feed.pinned = isPinned;
            var url = this.BASE_URL + '/feeds/' + feedId;
            return this.http.patch(url, {
                pinned: isPinned
            });
        }
    };


    FeedResource.prototype.patch = function (feedId, diff) {
        var feed = this.getById(feedId);

        if (feed) {
            Object.keys(diff).forEach(function (key) {
                feed[key] = diff[key];
            });
            var url = this.BASE_URL + '/feeds/' + feedId;
            return this.http.patch(url, diff);
        }
    };


    FeedResource.prototype.toggleFullText = function (feedId) {
        var feed = this.getById(feedId);

        return this.patch(feedId, {fullTextEnabled: !feed.fullTextEnabled});
    };


    return new FeedResource($http, BASE_URL, $q);
});
