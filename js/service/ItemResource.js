/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('ItemResource', function (Resource, $http, BASE_URL, ITEM_BATCH_SIZE) {
    'use strict';

    var ItemResource = function ($http, BASE_URL, ITEM_BATCH_SIZE) {
        Resource.call(this, $http, BASE_URL);
        this.batchSize = ITEM_BATCH_SIZE;
        this.clear();
    };

    ItemResource.prototype = Object.create(Resource.prototype);

    ItemResource.prototype.clear = function () {
        this.starredCount = 0;
        this.lowestId = 0;
        this.highestId = 0;
        this.fingerprints = {};
        Resource.prototype.clear.call(this);
    };

    ItemResource.prototype.receive = function (value, channel) {
        switch (channel) {
            case 'newestItemId':
                this.newestItemId = value;
                break;

            case 'starred':
                this.starredCount = value;
                break;

            default:
                var self = this;
                var importValues = [];
                value.forEach(function (item) {
                    // initialize lowest and highest id
                    if (self.lowestId === 0) {
                        self.lowestId = item.id;
                    }
                    if (self.highestId === 0) {
                        self.highestId = item.id;
                    }

                    if (item.id > self.highestId) {
                        self.highestId = item.id;
                    }
                    if (item.id < self.lowestId) {
                        self.lowestId = item.id;
                    }

                    // filter out duplicates
                    if (self.fingerprints[item.fingerprint] === undefined) {
                        self.fingerprints[item.fingerprint] = true;
                        importValues.push(item);
                    }
                });

                Resource.prototype.receive.call(this, importValues, channel);
        }
    };


    ItemResource.prototype.getNewestItemId = function () {
        return this.newestItemId;
    };


    ItemResource.prototype.getStarredCount = function () {
        return this.starredCount;
    };


    ItemResource.prototype.star = function (itemId, isStarred) {
        if (isStarred === undefined) {
            isStarred = true;
        }

        var it = this.get(itemId);
        var url = this.BASE_URL +
            '/items/' + it.feedId + '/' + it.guidHash + '/star';

        it.starred = isStarred;

        if (isStarred) {
            this.starredCount += 1;
        } else {
            this.starredCount -= 1;
        }

        return this.http({
            url: url,
            method: 'POST',
            data: {
                isStarred: isStarred
            }
        });
    };


    ItemResource.prototype.toggleStar = function (itemId) {
        if (this.get(itemId).starred) {
            this.star(itemId, false);
        } else {
            this.star(itemId, true);
        }
    };


    ItemResource.prototype.markItemRead = function (itemId, isRead) {
        if (isRead === undefined) {
            isRead = true;
        }

        this.get(itemId).unread = !isRead;

        return this.http({
            url: this.BASE_URL + '/items/' + itemId + '/read',
            method: 'POST',
            data: {
                isRead: isRead
            }
        });
    };


    ItemResource.prototype.markItemsRead = function (itemIds) {
        var self = this;

        itemIds.forEach(function (itemId) {
            self.get(itemId).unread = false;
        });

        return this.http({
            url: this.BASE_URL + '/items/read/multiple',
            method: 'POST',
            data: {
                itemIds: itemIds
            }
        });
    };


    ItemResource.prototype.markFeedRead = function (feedId, read) {
        if (read === undefined) {
            read = true;
        }

        var items = this.values.filter(function (element) {
            return element.feedId === feedId;
        });

        items.forEach(function (item) {
            item.unread = !read;
        });

        return this.http.post(this.BASE_URL + '/feeds/' + feedId + '/read', {
            highestItemId: this.getNewestItemId()
        });
    };


    ItemResource.prototype.markRead = function () {
        this.values.forEach(function (item) {
            item.unread = false;
        });

        return this.http({
            url: this.BASE_URL + '/items/read',
            method: 'POST',
            data: {
                highestItemId: this.getNewestItemId()
            }
        });
    };


    ItemResource.prototype.autoPage = function (type, id, oldestFirst, showAll, search) {
        var offset;

        if (oldestFirst) {
            offset = this.highestId;
        } else {
            offset = this.lowestId;
        }

        return this.http({
            url: this.BASE_URL + '/items',
            method: 'GET',
            params: {
                type: type,
                id: id,
                offset: offset,
                limit: this.batchSize,
                oldestFirst: oldestFirst,
                showAll: showAll,
                search: search
            }
        });
    };


    ItemResource.prototype.importArticles = function (json) {
        return this.http({
            url: this.BASE_URL + '/feeds/import/articles',
            method: 'POST',
            data: {
                json: json
            }
        }).then(function (response) {
            return response.data;
        });
    };


    return new ItemResource($http, BASE_URL, ITEM_BATCH_SIZE);
});