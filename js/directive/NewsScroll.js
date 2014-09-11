/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsScroll', function ($timeout) {
    'use strict';

    // autopaging
    var autoPage = function (enabled, limit, elem, scope) {
        if (enabled) {
            var counter = 0;
            var articles = elem.find('.item');

            for (var i = articles.length - 1; i >= 0; i -= 1) {
                var item = $(articles[i]);


                // if the counter is higher than the size it means
                // that it didnt break to auto page yet and that
                // there are more items, so break
                if (counter >= limit) {
                    break;
                }

                // this is only reached when the item is not is
                // below the top and we didnt hit the factor yet so
                // autopage and break
                if (item.position().top < 0) {
                    scope.$apply(scope.newsScrollAutoPage);
                    break;
                }

                counter += 1;
            }
        }
    };

    // mark read
    var markRead = function (enabled, elem, scope) {
        if (enabled) {
            var ids = [];
            var articles = elem.find('.item:not(.read)');

            articles.each(function(index, article) {
                var item = $(article);

                if (item.position().top <= -50) {
                    ids.push(parseInt(item.data('id'), 10));
                } else {
                    return false;
                }
            });

            scope.itemIds = ids;
            scope.$apply(scope.newsScrollMarkRead);
        }
    };

    return {
        restrict: 'A',
        scope: {
            'newsScrollAutoPage': '&',
            'newsScrollMarkRead': '&',
            'newsScrollEnabledMarkRead': '=',
            'newsScrollEnabledAutoPage': '=',
            'newsScrollMarkReadTimeout': '@',  // optional, defaults to 1 second
            'newsScrollTimeout': '@',  // optional, defaults to 1 second
            'newsScrollAutoPageWhenLeft': '@'  // optional, defaults to 50
        },
        link: function (scope, elem) {
            var allowScroll = true;

            var scrollTimeout = scope.newsScrollTimeout || 1;
            var markReadTimeout = scope.newsScrollMarkReadTimeout || 1;
            var autoPageLimit = scope.newsScrollAutoPageWhenLeft || 50;

            var scrollHandler = function () {
                // allow only one scroll event to trigger at once
                if (allowScroll) {
                    allowScroll = false;

                    $timeout(function () {
                        allowScroll = true;
                    }, scrollTimeout*1000);

                    autoPage(scope.newsScrollEnabledAutoPage,
                             autoPageLimit,
                             elem,
                             scope);

                    // allow user to undo accidental scroll
                    $timeout(function () {
                        markRead(scope.newsScrollEnabledMarkRead,
                                 elem,
                                 scope);
                    }, markReadTimeout*1000);
                }

            };

            elem.on('scroll', scrollHandler);

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', function () {
                elem.off('scroll', scrollHandler);
            });
        }
    };
});