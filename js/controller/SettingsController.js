/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('SettingsController',
function ($route, SettingsResource, FeedResource) {
    'use strict';

    this.importing = false;
    this.opmlImportError = false;
    this.articleImportError = false;

    var set = function (key, value) {
        SettingsResource.set(key, value);

        if (['showAll', 'oldestFirst'].indexOf(key) >= 0) {
            $route.reload();
        }
    };


    this.toggleSetting = function (key) {
        set(key, !this.getSetting(key));
    };


    this.getSetting = function (key) {
        return SettingsResource.get(key);
    };


    this.feedSize = function () {
        return FeedResource.size();
    };


    // TBD
    this.importOpml = function (content) {
        console.log(content);
    };


    this.importArticles = function (content) {
        console.log(content);
    };

});