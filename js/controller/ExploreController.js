/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('ExploreController', function (sites, $rootScope, FeedResource) {
    'use strict';

    this.sites = sites;

    this.feedExists = function (url) {
    	return FeedResource.get(url) !== undefined;
    };

    this.subscribeTo = function (url) {
        $rootScope.$broadcast('addFeed', url);
    };

});