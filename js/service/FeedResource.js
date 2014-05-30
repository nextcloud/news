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
            this.folderIds = {};
            this.deleted = null;
        }


        receive (data) {
            super.receive(data);
            this.updateUnreadCache();
            this.updateFolderCache();
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


        updateFolderCache () {
            this.folderIds = {};

            for (let feed of this.values) {
                this.folderIds[feed.folderId] =
                    this.folderIds[feed.folderId] || [];
                this.folderIds[feed.folderId].push(feed);
            }
        }


        add (value) {
            super.add(value);
            if (value.id !== undefined) {
                this.ids[value.id] = this.hashMap[value.url];
            }
        }


        delete (url) {
            let feed = this.get(url);
            this.deleted = feed;
            delete this.ids[feed.id];

            super.delete(url);

            this.updateUnreadCache();
            this.updateFolderCache();

            return this.http.delete(`${this.BASE_URL}/feeds/${feed.id}`);
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
            return this.folderUnreadCount[folderId];
        }


        getByFolderId (folderId) {
            return this.folderIds[folderId] || [];
        }


        getById (feedId) {
            return this.ids[feedId];
        }


        rename (url, name) {
            let feed = this.get(url);
            feed.title = name;

            return this.http({
                method: 'POST',
                url: `${this.BASE_URL}/feeds/${feed.id}/rename`,
                data: {
                    feedTitle: name
                }
            });
        }


        move (url, folderId) {
            let feed = this.get(url);
            feed.folderId = folderId;

            this.updateFolderCache();

            return this.http({
                method: 'POST',
                url: `${this.BASE_URL}/feeds/${feed.id}/move`,
                data: {
                    parentFolderId: folderId
                }
            });

        }


        create (url, folderId, title=null) {
            if (title) {
                title = title.toUpperCase();
            }

            let feed = {
                url: url,
                folderId: folderId,
                title: title
            };

            if (!this.get(url)) {
                this.add(feed);
            }

            this.updateFolderCache();

            return this.http({
                method: 'POST',
                url: `${this.BASE_URL}/feeds`,
                data: {
                    url: url,
                    parentFolderId: folderId,
                    title: title
                }
            });
        }


        undoDelete () {
            if (this.deleted) {
                this.add(this.deleted);

                return this.http.post(
                    `${this.BASE_URL}/feeds/${this.deleted.id}/restore`
                );
            }

            this.updateFolderCache();
            this.updateUnreadCache();
        }


    }

    return new FeedResource($http, BASE_URL);
});