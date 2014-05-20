/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FeedResource', function (Resource, $http) {
    'use strict';

    var FeedResource = function ($http) {
        Resource.call(this, 'url', $http);
    };

    FeedResource.prototype = Object.create(Resource.prototype);

    return new FeedResource($http);
});