/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('ItemResource', function (Resource, $http) {
    'use strict';

    var ItemResource = function ($http) {
        Resource.call(this, 'id', $http);
    };

    ItemResource.prototype = Object.create(Resource.prototype);

    ItemResource.prototype.receive = function (value, channel) {
        switch (channel) {

        case 'newestItemId':
            this.newestItemId = value;
            break;

        case 'starred':
            this.starredCount = value;
            break;
        default:
            Resource.prototype.receive.call(this, value, channel);
        }
    };

    ItemResource.prototype.getNewestItemId = function () {
        return this.newestItemId;
    };

    ItemResource.prototype.getStarredCount = function () {
        return this.starredCount;
    };


    return new ItemResource($http);
});