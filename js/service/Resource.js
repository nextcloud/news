/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('Resource', () => {
    'use strict';

    class Resource {

        constructor (id, http) {
            this.id = id;
            this.values = [];
            this.hashMap = {};
            this.http = http;
        }

        receive (values) {
            values.forEach((value) => {
                this.add(value);
            });
        }

        add (value) {
            let existing = this.hashMap[value[this.id]];

            if (existing === undefined) {
                this.values.push(value);
                this.hashMap[value[this.id]] = value;
            } else {
                // copy values from new to old object if it exists already
                for (let key in value) {
                    if (value.hasOwnProperty(key)) {
                        existing[key] = value[key];
                    }
                }
            }
        }

        size () {
            return this.values.length;
        }

        get (id) {
            return this.hashMap[id];
        }

        delete (id) {
            // find index of object that should be deleted
            let deleteAtIndex;

            for (let i = 0; i < this.values.length; i += 1) {
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
        }

        clear () {
            this.hashMap = {};

            // http://stackoverflow.com/questions/1232040
            // this is the fastes way to empty an array when you want to keep
            // the reference around
            while (this.values.length > 0) {
                this.values.pop();
            }
        }

        getAll () {
            return this.values;
        }
    }

    return Resource;
});