/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('ItemResource', (Resource, $http, BASE_URL, ITEM_BATCH_SIZE) => {
    'use strict';

    class ItemResource extends Resource {


        constructor ($http, BASE_URL, ITEM_BATCH_SIZE) {
            super($http, BASE_URL);
            this.starredCount = 0;
            this.batchSize = ITEM_BATCH_SIZE;
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


        toggleStar (itemId) {
            if (this.get(itemId).starred) {
                this.star(itemId, false);
            } else {
                this.star(itemId, true);
            }
        }


        markItemRead (itemId, isRead=true) {
            this.get(itemId).unread = !isRead;
            return this.http({
                url: `${this.BASE_URL}/items/${itemId}/read`,
                method: 'POST',
                data: {
                    isRead: isRead
                }
            });
        }


        markItemsRead (itemIds) {
            for (let itemId of itemIds) {
                this.get(itemId).unread = false;
            }

            return this.http({
                url: `${this.BASE_URL}/items/read/multiple`,
                method: 'POST',
                data: {
                    itemIds: itemIds
                }
            });
        }


        markFeedRead (feedId, read=true) {
            for (let item of this.values.filter(i => i.feedId === feedId)) {
                item.unread = !read;
            }
            return this.http.post(`${this.BASE_URL}/feeds/${feedId}/read`);
        }


        markRead () {
            for (let item of this.values) {
                item.unread = false;
            }
            return this.http.post(`${this.BASE_URL}/items/read`);
        }


        autoPage (type, id) {
            return this.http({
                url: `${this.BASE_URL}/items`,
                method: 'GET',
                params: {
                    type: type,
                    id: id,
                    offset: this.size(),
                    limit: this.batchSize
                }
            });
        }

    }

    return new ItemResource($http, BASE_URL, ITEM_BATCH_SIZE);
});