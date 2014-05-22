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
    let autoPage = (enabled, limit, callback, elem) => {
        if (enabled) {
            let counter = 0;
            for (let item of reverse(elem.find('.feed_item'))) {
                item = $(item);


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
                    callback();
                    break;
                }

                counter += 1;
            }
        }
    };

    // mark read
    let markRead = (enabled, callback, elem) => {
        if (enabled) {
            let ids = [];

            for (let item of elem.find('.feed_item:not(.read)')) {
                item = $(item);

                if (item.position().top <= -50) {
                    ids.push(parseInt(item.data('id'), 10));
                } else {
                    break;
                }
            }

            callback(ids);
        }
    };

    return {
        restrict: 'A',
        scope: {
            'newsScrollAutoPage': '&',
            'newsScrollMarkRead': '&',
            'newsScrollEnabledMarkRead': '=',
            'newsScrollEnableAutoPage': '=',
            'newsScrollMarkReadTimeout': '@',  // optional, defaults to 1 second
            'newsScrollTimeout': '@',  // optional, defaults to 1 second
            'newsScrollAutoPageWhenLeft': '@'  // optional, defaults to 50
        },
        link: (scope, elem) => {
            let allowScroll = true;

            scope.newsScrollTimeout = scope.newsScrollTimeout || 1;
            scope.newsScrollMarkReadTimeout =
                scope.newsScrollMarkReadTimeout || 1;
            scope.newsScrollAutoPageWhenLeft =
                scope.newsScrollAutoPageWhenLeft || 50;

            let scrollHandler = () => {
                // allow only one scroll event to trigger at once
                if (allowScroll) {
                    allowScroll = false;

                    $timeout(() => {
                        allowScroll = true;
                    }, scope.newsScrollTimeout*1000);

                    autoPage(scope.newsScrollEnableAutoPage,
                             scope.newsScrollAutoPageWhenLeft,
                             scope.newsScrollAutoPage,
                             elem);

                    // allow user to undo accidental scroll
                    $timeout(() => {
                        markRead(scope.newsScrollEnabledMarkRead,
                                 scope.newsScrollMarkRead,
                                 elem);
                    }, scope.newsScrollMarkReadTimeout*1000);
                }

            });

            elem.on('scroll', scrollHandler);

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', () => {
                elem.off('scroll', scrollHandler);
            });
        }
    };
});