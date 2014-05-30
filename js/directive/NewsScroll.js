/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsScroll', ($timeout) => {
    'use strict';

    // autopaging
    let autoPage = (enabled, limit, elem, scope) => {
        if (enabled) {
            let counter = 0;
            let articles = elem.find('.item');

            for (let i = articles.length - 1; i >= 0; i -= 1) {
                let item = $(articles[i]);


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
    let markRead = (enabled, elem, scope) => {
        if (enabled) {
            let ids = [];

            let articles = elem.find('.item:not(.read)');

            for (let i = 0; i < articles.length; i += 1) {
                let item = $(articles[i]);

                if (item.position().top <= -50) {
                    ids.push(parseInt(item.data('id'), 10));
                } else {
                    break;
                }
            }

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
        link: (scope, elem) => {
            let allowScroll = true;

            let scrollTimeout = scope.newsScrollTimeout || 1;
            let markReadTimeout = scope.newsScrollMarkReadTimeout || 1;
            let autoPageLimit = scope.newsScrollAutoPageWhenLeft || 50;

            let scrollHandler = () => {
                // allow only one scroll event to trigger at once
                if (allowScroll) {
                    allowScroll = false;

                    $timeout(() => {
                        allowScroll = true;
                    }, scrollTimeout*1000);

                    autoPage(scope.newsScrollEnabledAutoPage,
                             autoPageLimit,
                             elem,
                             scope);

                    // allow user to undo accidental scroll
                    $timeout(() => {
                        markRead(scope.newsScrollEnabledMarkRead,
                                 elem,
                                 scope);
                    }, markReadTimeout*1000);
                }

            };

            elem.on('scroll', scrollHandler);

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', () => {
                elem.off('scroll', scrollHandler);
            });
        }
    };
});