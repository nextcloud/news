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
        link: function (scope, elem) {
            var plugins = News.getArticleActionPlugins();
            scope.plugins = [];

            for (var i=0; i<plugins.length; i+=1) {
                var plugin = new plugins[i](elem, scope.article);
                scope.plugins.push(plugin);
            }
        }
    };
});