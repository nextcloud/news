/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsArticleActions', function () {
    'use strict';
    return {
        restrict: 'E',
        templateUrl: 'articleaction.html',
        scope: {
            'article': '='
        },
        replace: true,
        link: function (scope) {
            scope.plugins = News.getArticleActionPlugins();
            scope.pluginClick = function (pluginId, event, article) {
                News.getArticleActionPluginById(pluginId)
                    .onClick(event, article);
            };
        }
    };
});