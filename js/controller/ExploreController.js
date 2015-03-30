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

    this.feedExists = function (location) {
        return FeedResource.getByLocation(location) !== undefined;
    };

    this.subscribeTo = function (location) {
        $rootScope.$broadcast('addFeed', location);
    };

    this.isCategoryShown = function (data) {
        return data.filter(function (element) {
            return FeedResource.getByLocation(element.feed) === undefined;
        }).length > 0;
    };

});