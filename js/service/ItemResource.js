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
            this.starredCount = 0;
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


        star (itemId, isStarred=true) {
            let it = this.get(itemId);
            let url = `${this.BASE_URL}/items/${it.feedId}/${it.guidHash}/star`;

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
        }


        read (itemId, isRead=true) {
            this.get(itemId).unread = !isRead;
            return this.http({
                url: `${this.BASE_URL}/items/${itemId}/read`,
                method: 'POST',
                data: {
                    isRead: isRead
                }
            });
        }


        readFeed (feedId, read=true) {
            for (let item of this.values.filter(i => i.feedId === feedId)) {
                item.unread = !read;
            }
            return this.http.post(`${this.BASE_URL}/feeds/${feedId}/read`);
        }


        readAll () {
            for (let item of this.values) {
                item.unread = false;
            }
            return this.http.post(`${this.BASE_URL}/items/read`);
        }


        keepUnread (itemId) {
            this.get(itemId).keepUnread = true;
            return this.read(itemId, false);
        }


    }

    return new ItemResource($http, BASE_URL);
});