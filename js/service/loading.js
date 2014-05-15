/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
app.service('Loading', function () {
    'use strict';

    this.loading = false;

    this.setLoading = function (isLoading) {
        this.loading = isLoading;
    };

    this.isLoading = function () {
        return this.loading;
    };

});