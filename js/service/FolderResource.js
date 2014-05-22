/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FolderResource', (Resource, $http, BASE_URL) => {
    'use strict';

    class FolderResource extends Resource {

        constructor ($http, BASE_URL) {
            super($http, BASE_URL, 'name');
        }

    }

    return new FolderResource($http, BASE_URL);
});