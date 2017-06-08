/**
 * Nextcloud - News
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
        this.ids = {};
    };

    FolderResource.prototype = Object.create(Resource.prototype);


    FolderResource.prototype.add = function (value) {
        Resource.prototype.add.call(this, value);
        if (value.id !== undefined) {
            this.ids[value.id] = this.hashMap[value.name];
        }
    };

    FolderResource.prototype.clear = function () {
        Resource.prototype.clear.call(this);
        this.ids = {};
    };

    FolderResource.prototype.delete = function (name) {
        var folder = this.get(name);
        if (folder !== undefined && folder.id) {
            delete this.ids[folder.id];
        }

        Resource.prototype.delete.call(this, name);

        return folder;
    };

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
        var self = this;

        return this.http({
            url: this.BASE_URL + '/folders/' + folder.id + '/rename',
            method: 'POST',
            data: {
                folderName: toFolderName
            }
        }).then(function () {
            folder.name = toFolderName;
            delete self.hashMap[folderName];
            self.hashMap[toFolderName] = folder;
        }, function (response) {
            return response.data.message;
        });
    };

    FolderResource.prototype.getById = function (id) {
        return this.ids[id];
    };

    FolderResource.prototype.create = function (folderName) {
        folderName = folderName.trim();
        var folder = {
            name: folderName
        };

        this.add(folder);

        return this.http({
            url: this.BASE_URL + '/folders',
            method: 'POST',
            data: {
                folderName: folderName
            }
        }).then(function (response) {
            return response.data;
        }, function (response) {
            folder.error = response.data.message;
        });
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