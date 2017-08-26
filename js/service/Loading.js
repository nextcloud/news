/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
Loading.$inject = [];

export default function Loading() {
    'use strict';

    this.loading = {
        global: false,
        content: false,
        autopaging: false
    };

    this.setLoading = function (area, isLoading) {
        this.loading[area] = isLoading;
    };

    this.isLoading = function (area) {
        return this.loading[area];
    };

};