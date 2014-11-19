/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('ExploreController', function (sites, $rootScope) {
    'use strict';

    this.sites = sites;
    console.log(sites);

    this.subscribeTo = function (url) {
        $rootScope.$broadcast('addFeed', url);
    };

});