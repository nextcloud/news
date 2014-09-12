/**
 * ownCloud - News
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
        this.unreadCount = 0;
        this.folderUnreadCount = {};
        this.folderIds = {};
        this.deleted = null;
        this.$q = $q;
    };

    FeedResource.prototype = Object.create(Resource.prototype);

    FeedResource.prototype.receive = function (data) {
        Resource.prototype.receive.call(this, data);
        this.updateUnreadCache();
        this.updateFolderCache();
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
    };


    FeedResource.prototype.delete = function (url) {
        var feed = this.get(url);
        this.deleted = feed;
        delete this.ids[feed.id];

        Resource.prototype.delete.call(this, url);

        this.updateUnreadCache();
        this.updateFolderCache();

        return this.http.delete(this.BASE_URL + '/feeds/' + feed.id);
    };


    FeedResource.prototype.markRead = function () {
        this.values.forEach(function (feed) {
            feed.unreadCount = 0;
        });

        this.unreadCount = 0;
        this.folderUnreadCount = {};
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


    FeedResource.prototype.rename = function (url, name) {
        var feed = this.get(url);
        feed.title = name;

        return this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds/' + feed.id + '/rename',
            data: {
                feedTitle: name
            }
        });
    };


    FeedResource.prototype.move = function (feedId, folderId) {
        var feed = this.getById(feedId);
        feed.folderId = folderId;

        this.updateFolderCache();
        this.updateUnreadCache();

        return this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds/' + feed.id + '/move',
            data: {
                parentFolderId: folderId
            }
        });

    };


    FeedResource.prototype.create = function (url, folderId, title) {
        url = url.trim();
        if (!url.startsWith('http')) {
            url = 'http://' + url;
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

        var deferred = this.$q.defer();

        this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds',
            data: {
                url: url,
                parentFolderId: folderId || 0,
                title: title
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function (data) {
            feed.faviconLink = '';
            feed.error = data.message;
            deferred.reject();
        });

        return deferred.promise;
    };


    FeedResource.prototype.undoDelete = function () {
        if (this.deleted) {
            this.add(this.deleted);

            return this.http.post(
                this.BASE_URL + '/feeds/' + this.deleted.id + '/restore'
            );
        }

        this.updateFolderCache();
        this.updateUnreadCache();
    };


    return new FeedResource($http, BASE_URL, $q);
});