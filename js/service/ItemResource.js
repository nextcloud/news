/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('ItemResource', (Resource, $http, BASE_URL) => {
    'use strict';

    class ItemResource extends Resource {

        constructor ($http, BASE_URL) {
            super($http, BASE_URL);
        }

        receive (value, channel) {
            switch (channel) {

            case 'newestItemId':
                this.newestItemId = value;
                break;

            case 'starred':
                this.starredCount = value;
                break;
            default:
                super.receive(value, channel);
            }
        }

        getNewestItemId () {
            return this.newestItemId;
        }

        getStarredCount () {
            return this.starredCount;
        }

        markRead (itemId, read=true) {
            this.get(itemId).unread = !read;
            //http.get();
        }

        markFeedRead (feedId) {
            for (let item in this.values.filter(i => i.feedId === feedId)) {
                this.markRead(item);
            }
        }

    }

    return new ItemResource($http, BASE_URL);
});