/**
 * Nextcloud - News
 *
 * @author Marco Nassabain <marco.nassabain@hotmail.com>
 */
app.factory('UserResource', function (Resource, $http, BASE_URL) {
    'use strict';

    var UserResource = function ($http, BASE_URL) {
        Resource.call(this, $http, BASE_URL);
    };

    UserResource.prototype = Object.create(Resource.prototype);

    UserResource.prototype.getUsers = function (search) {
        console.log(search);
        return this.http({
            url: OC.linkToOCS(`apps/files_sharing/api/v1/sharees?search=${search}&itemType=file`, 1),
            method: 'GET',
        }).then(function(response) {
            return response.data;
        });
    };


    return new UserResource($http, BASE_URL);
});