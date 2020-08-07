/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('Resource', function () {
    'use strict';

    var Resource = function (http, BASE_URL, id) {
        this.id = id || 'id';
        this.values = [];
        this.hashMap = {};
        this.http = http;
        this.BASE_URL = BASE_URL;
    };


    Resource.prototype.receive = function (objs) {
        var self = this;
        objs.forEach(function (obj) {
            self.add(obj);
        });
    };


    Resource.prototype.add = function (obj) {
        var existing = this.hashMap[obj[this.id]];

        if (existing === undefined) {
            this.values.push(obj);
            this.hashMap[obj[this.id]] = obj;
        } else {
            // copy values from new to old object if it exists already
            Object.keys(obj).forEach(function (key) {
                existing[key] = obj[key];
            });
        }
    };


    Resource.prototype.size = function () {
        return this.values.length;
    };


    Resource.prototype.get = function (id) {
        return this.hashMap[id];
    };


    Resource.prototype.delete = function (id) {
        // find index of object that should be deleted
        var self = this;
        var deleteAtIndex = this.values.findIndex(function (element) {
            return element[self.id] === id;
        });

        if (deleteAtIndex !== undefined) {
            this.values.splice(deleteAtIndex, 1);
        }

        if (this.hashMap[id] !== undefined) {
            delete this.hashMap[id];
        }
    };


    Resource.prototype.clear = function () {
        this.hashMap = {};

        // http://stackoverflow.com/questions/1232040
        // this is the fastes way to empty an array when you want to keep
        // the reference around
        while (this.values.length > 0) {
            this.values.pop();
        }
    };


    Resource.prototype.getAll = function () {
        return this.values;
    };


    return Resource;
});