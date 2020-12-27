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
        return this.http({
            url: OC.linkToOCS(`cloud/users?search=${search}&offset=0&limit=5`, 2),
            method: 'GET',
        }).then(function(response) {
            return response.data;
        });
    };


    return new UserResource($http, BASE_URL);
});