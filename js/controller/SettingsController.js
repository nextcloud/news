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
function ($route, $q, SettingsResource, ItemResource, OPMLParser,
          OPMLImporter, Publisher) {
    'use strict';

    this.isOPMLImporting = false;
    this.isArticlesImporting = false;
    this.opmlImportError = false;
    this.articleImportError = false;

    var set = function (key, value) {
        SettingsResource.set(key, value);

        if (['showAll', 'oldestFirst', 'compact'].indexOf(key) >= 0) {
            $route.reload();
        }
    };

    this.toggleSetting = function (key) {
        set(key, !this.getSetting(key));
    };

    this.getSetting = function (key) {
        return SettingsResource.get(key);
    };

    this.importOPML = function (content) {
        this.opmlImportError = false;
        this.articleImportError = false;

        try {
            this.isOPMLImporting = false;
            var parsedContent = OPMLParser.parse(content);

            var self = this;
            var jobSize = 5;

            OPMLImporter.importFolders(parsedContent)
            .then(function (feedQueue) {
                return OPMLImporter.importFeedQueue(feedQueue, jobSize);
            }).finally(function () {
                self.isOPMLImporting = false;
            });

        } catch (error) {
            this.isOPMLImporting = false;
            this.opmlImportError = true;
        }
    };

    this.importArticles = function (content) {
        this.opmlImportError = false;
        this.articleImportError = false;

        try {
            this.isArticlesImporting = true;
            var articles = JSON.parse(content);

            var self = this;
            ItemResource.importArticles(articles).success(function (data) {
                Publisher.publishAll(data);
            }).finally(function () {
                self.isArticlesImporting = false;
            });

        } catch (error) {
            this.articleImportError = true;
            this.isArticlesImporting = false;
        }
    };

});