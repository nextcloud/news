/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('ItemResource', (Resource, $http) => {
    'use strict';

    class ItemResource extends Resource {

        constructor ($http) {
            super($http);
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

    }

    return new ItemResource($http);
});