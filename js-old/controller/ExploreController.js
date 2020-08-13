/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('ExploreController', function (sites, $rootScope, FeedResource, SettingsResource, $location) {
    'use strict';

    this.sites = sites;
    // join all sites
    this.feeds = Object.keys(sites).map(function (key) {
        return [key, sites[key]];
    }).reduce(function (xs, x) {
        var category = x[0];
        var feedList = x[1];
        feedList.forEach(function (feed) {
            feed.category = category;
        });
        return xs.concat(feedList);
    }, []);

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

    this.getSupportedLanguageCodes = function () {
        return SettingsResource.getSupportedLanguageCodes();
    };

    this.getCurrentLanguageCode = function () {
        var language = $location.search().lang;
        if (!language) {
            language = SettingsResource.get('language');
        }
        return language;
    };

    this.showLanguage = function (languageCode) {
        $location.url('/explore/?lang=' + languageCode);
    };

    this.selectedLanguageCode = this.getCurrentLanguageCode();
});
