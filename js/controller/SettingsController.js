/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('SettingsController', function (Settings, $route, FeedResource) {
    'use strict';

    this.importing = false;
    this.opmlImportError = false;
    this.articleImportError = false;

    let set = (key, value) => {
        Settings.set(key, value);

        if (['showAll', 'oldestFirst'].indexOf(key) >= 0) {
            $route.reload();
        }
    };


    this.toggleSetting = (key) => {
        set(key, !this.getSetting(key));
    };


    this.getSetting = (key) => {
        return Settings.get(key);
    };


    this.importOpml = (content) => {
        console.log(content);
    };


    this.importArticles = (content) => {
        console.log(content);
    };


    this.feedSize = () => {
        return FeedResource.size();
    };


});