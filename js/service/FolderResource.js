/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('FolderResource', function (Resource, $http, BASE_URL, $q) {
    'use strict';

    var FolderResource = function ($http, BASE_URL, $q) {
        Resource.call(this, $http, BASE_URL, 'name');
        this.deleted = null;
        this.$q = $q;
    };

    FolderResource.prototype = Object.create(Resource.prototype);


    FolderResource.prototype.toggleOpen = function (folderName) {
        var folder = this.get(folderName);
        folder.opened = !folder.opened;

        return this.http({
            url: this.BASE_URL + '/folders/' + folder.id + '/open',
            method: 'POST',
            data: {
                folderId: folder.id,
                open: folder.opened
            }
        });
    };


    FolderResource.prototype.rename = function (folderName, toFolderName) {
        var folder = this.get(folderName);
        var deferred = this.$q.defer();
        var self = this;

        this.http({
            url: this.BASE_URL + '/folders/' + folder.id + '/rename',
            method: 'POST',
            data: {
                folderName: toFolderName
            }
        }).success(function () {
            folder.name = toFolderName;
            delete self.hashMap[folderName];
            self.hashMap[toFolderName] = folder;

            deferred.resolve();
        }).error(function (data) {
            deferred.reject(data.message);
        });

        return deferred.promise;
    };


    FolderResource.prototype.create = function (folderName) {
        folderName = folderName.trim();
        var folder = {
            name: folderName
        };

        this.add(folder);

        var deferred = this.$q.defer();

        this.http({
            url: this.BASE_URL + '/folders',
            method: 'POST',
            data: {
                folderName: folderName
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function (data) {
            folder.error = data.message;
        });

        return deferred.promise;
    };


    FolderResource.prototype.reversiblyDelete = function (name) {
        var folder = this.get(name);
        var id = folder.id;
        folder.deleted = true;
        return this.http.delete(this.BASE_URL + '/folders/' + id);
    };


    FolderResource.prototype.undoDelete = function (name) {
        var folder = this.get(name);
        var id = folder.id;
        folder.deleted = false;
        return this.http.post(this.BASE_URL + '/folders/' + id + '/restore');
    };


    return new FolderResource($http, BASE_URL, $q);
});