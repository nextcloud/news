/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FolderResource', function (Resource, $http) {
    'use strict';

    var FolderResource = function ($http) {
        Resource.call(this, 'name', $http);
    };

    FolderResource.prototype = Object.create(Resource.prototype);

    return new FolderResource($http);
});