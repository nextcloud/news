/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

 /*jshint unused:false*/
app.service('Settings', function ($http, BASE_URL) {
    'use strict';

    this.settings = {};

    this.receive = (data) => {
        for (let [key, value] of items(data)) {
            this.settings[key] = value;
        }
    };

    this.get = (key) => {
        return this.settings[key];
    };

    this.set = (key, value) => {
        this.settings[key] = value;

        let data = {};
        data[key] = value;

        return $http({
                url: `${BASE_URL}/settings`,
                method: 'POST',
                data: data
            });
    };

});