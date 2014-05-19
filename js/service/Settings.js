/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.service('Settings', function () {
    'use strict';

    this.settings = {};

    this.receive = function (data) {
        var key;
        for (key in data) {
            if (data.hasOwnProperty(key)) {
                this.settings[key] = data[key];
            }
        }
    };

    this.get = function (key) {
        return this.settings[key];
    };

    this.set = function (key, value) {
        this.settings[key] = value;
    };

});