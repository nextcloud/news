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


        star (itemId, star=true) {
            let item = this.get(itemId);
            let base = this.BASE_URL;
            let url = `${base}/items/${item.feedId}/${item.guidHash}/star`;

            item.starred = star;

            return this.http({
                url: url,
                method: 'POST',
                data: {
                    isStarred: star
                }
            });
        }


        read (itemId, read=true) {
            this.get(itemId).unread = !read;
            return this.http({
                url: `${this.BASE_URL}/items/${itemId}/read`,
                method: 'POST',
                data: {
                    isRead: read
                }
            });
        }


        keepUnread (itemId) {
            this.get(itemId).keepUnread = true;
            return this.read(itemId, false);
        }


        readFeed (feedId, read=true) {
            for (let item of this.values.filter(i => i.feedId === feedId)) {
                item.unread = !read;
            }
            return this.http.post(`${this.BASE_URL}/feeds/${feedId}/read`);
        }


    }

    return new ItemResource($http, BASE_URL);
});