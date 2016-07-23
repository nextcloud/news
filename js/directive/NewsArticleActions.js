/**
 * Nextcloud - News
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
        restrict: 'A',
        scope: {
            newsArticleActions: '=',
            noPlugins: '='
        },
        link: function (scope, elem) {
            var plugins = News.getArticleActionPlugins();

            for (var i=0; i<plugins.length; i+=1) {
                plugins[i](elem, scope.newsArticleActions);
            }

            scope.noPlugins = plugins.length === 0;
        }
    };
});
