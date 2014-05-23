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
        }


        markFeedRead (feedId) {
            this.ids[feedId].unreadCount = 0;
        }


        markItemOfFeedRead (feedId) {
            this.ids[feedId].unreadCount -= 1;
        }


        markItemOfFeedUnread (feedId) {
            this.ids[feedId].unreadCount += 1;
        }


        getUnreadCount () {
            return this.values.reduce((sum, feed) => sum + feed.unreadCount, 0);
        }


    }

    return new FeedResource($http, BASE_URL);
});