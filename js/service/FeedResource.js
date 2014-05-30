/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FeedResource', (Resource, $http, BASE_URL) => {
    'use strict';

    class FeedResource extends Resource {

        constructor ($http, BASE_URL) {
            super($http, BASE_URL, 'url');
            this.ids = {};
            this.unreadCount = 0;
            this.folderUnreadCount = {};
        }


        receive (data) {
            super.receive(data);
            this.updateUnreadCache();
        }


        updateUnreadCache () {
            this.unreadCount = 0;
            this.folderUnreadCount = {};

            for (let value of this.values) {
                if (value.unreadCount) {
                    this.unreadCount += value.unreadCount;
                }
                if (value.folderId !== undefined) {
                    this.folderUnreadCount[value.folderId] =
                        this.folderUnreadCount[value.folderId] || 0;
                    this.folderUnreadCount[value.folderId] += value.unreadCount;
                }
            }
        }


        add (value) {
            super.add(value);
            if (value.id !== undefined) {
                this.ids[value.id] = this.hashMap[value.url];
            }
        }


        delete (id) {
            let feed = this.get(id);
            delete this.ids[feed.id];
            super.delete(id);
        }


        markRead () {
            for (let feed of this.values) {
                feed.unreadCount = 0;
            }
            this.unreadCount = 0;
            this.folderUnreadCount = {};
        }


        markFeedRead (feedId) {
            this.ids[feedId].unreadCount = 0;
            this.updateUnreadCache();
        }


        markFolderRead (folderId) {
            for (let feed of this.values) {
                if (feed.folderId === folderId) {
                    feed.unreadCount = 0;
                }
            }
            this.updateUnreadCache();
        }


        markItemOfFeedRead (feedId) {
            this.ids[feedId].unreadCount -= 1;
            this.updateUnreadCache();
        }


        markItemsOfFeedsRead (feedIds) {
            for (let feedId of feedIds) {
                this.ids[feedId].unreadCount -= 1;
            }
            this.updateUnreadCache();
        }


        markItemOfFeedUnread (feedId) {
            this.ids[feedId].unreadCount += 1;
            this.updateUnreadCache();
        }


        getUnreadCount () {
            return this.unreadCount;
        }


        getFolderUnreadCount (folderId) {
            return this.folderUnreadCount[folderId] || 0;
        }


        getByFolderId (folderId) {
            return this.values.filter(v => v.folderId === folderId);
        }

        getById (feedId) {
            return this.ids[feedId];
        }
    }

    return new FeedResource($http, BASE_URL);
});