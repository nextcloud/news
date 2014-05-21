/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FeedResource', (Resource, $http) => {
    'use strict';

    class FeedResource extends Resource {
        constructor ($http) {
            super($http, 'url');
        }
    }

    return new FeedResource($http);
});