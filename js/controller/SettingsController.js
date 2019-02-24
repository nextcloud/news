/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('SettingsController', function ($route, $q, SettingsResource, ItemResource, OPMLParser, OPMLImporter,
                                               Publisher) {
    'use strict';
    this.isOPMLImporting = false;
    this.isArticlesImporting = false;
    this.opmlImportError = false;
    this.articleImportError = false;
    this.opmlImportEmptyError = false;
    var self = this;

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

    this.importOPML = function (fileContent) {
        self.opmlImportError = false;
        self.opmlImportEmptyError = false;
        self.articleImportError = false;

        try {
            this.isOPMLImporting = false;
            var parsedContent = OPMLParser.parse(fileContent);

            var jobSize = 5;

            if (parsedContent.folders.length === 0 &&
                parsedContent.feeds.length === 0) {
                self.opmlImportEmptyError = true;
            } else {
                OPMLImporter.importFolders(parsedContent).then(function (feedQueue) {
                    return OPMLImporter.importFeedQueue(feedQueue, jobSize);
                }).finally(function () {
                    self.isOPMLImporting = false;
                });
            }

        } catch (error) {
            this.opmlImportError = true;
            console.error(error);
            this.isOPMLImporting = false;
        }
    };

    this.importArticles = function (content) {
        this.opmlImportError = false;
        this.articleImportError = false;

        try {
            this.isArticlesImporting = true;
            var articles = JSON.parse(content);

            var self = this;
            ItemResource.importArticles(articles).then(function (data) {
                Publisher.publishAll(data);
            }).finally(function () {
                self.isArticlesImporting = false;
            });

        } catch (error) {
            console.error(error);
            this.articleImportError = true;
            this.isArticlesImporting = false;
        }
    };
});
