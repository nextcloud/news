/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('Model', function () {
    'use strict';

    var Model = function (id) {
        this.id = id;
        this.values = [];
        this.hashMap = {};
    };

    Model.prototype = {
        receive: function (values) {
            var self = this;
            values.forEach(function (value) {
                self.add(value);
            });
        },

        add: function (value) {
            var key,
                existing;

            existing = this.hashMap[value[this.id]];

            if (existing === undefined) {
                this.values.push(value);
                this.hashMap[value[this.id]] = value;
            } else {
                // copy values from new to old object if it exists already
                for (key in value) {
                    if (value.hasOwnProperty(key)) {
                        existing[key] = value[key];
                    }
                }
            }
        },

        size: function () {
            return this.values.length;
        },

        get: function (id) {
            return this.hashMap[id];
        },

        delete: function (id) {
            // find index of object that should be deleted
            var i,
                deleteAtIndex;

            for (i = 0; i < this.values.length; i += 1) {
                if (this.values[i][this.id] === id) {
                    deleteAtIndex = i;
                    break;
                }
            }

            if (deleteAtIndex !== undefined) {
                this.values.splice(deleteAtIndex, 1);
            }

            if (this.hashMap[id] !== undefined) {
                delete this.hashMap[id];
            }
        },

        clear: function () {
            this.hashMap = {};

            // http://stackoverflow.com/questions/1232040/how-to-empty-an-array-in-javascript
            // this is the fastes way to empty an array when you want to keep the
            // reference around
            while (this.values.length > 0) {
                this.values.pop();
            }
        },

        getAll: function () {
            return this.values;
        }
    };

    return Model;
});