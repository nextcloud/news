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
    let autoPage = (enabled, limit, items, callback) => {
        if (enabled) {
            let counter = 0;
            for (let item of reverse(items.find('.feed_item'))) {
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
    let markRead = (enabled, items, callback) => {
        if (enabled) {
            let ids = [];
            let unreadItems = items.find('.feed_item:not(.read)');

            for (let item of unreadItems) {
                item = $(item);

                if (item.position().top <= -50) {
                    ids.push(parseInt($(item).data('id'), 10));
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
            'newsScrollDisabledMarkRead': '=',
            'newsScrollDisabledAutoPage': '=',
            'newsScrollMarkReadTimeout': '@',  // optional, defaults to 1 second
            'newsScrollTimeout': '@',  // optional, defaults to 1 second
            'newsScrollAutoPageWhenLeft': '@',  // optional, defaults to 50
            'newsScrollItemsSelector': '@'  // optional, defaults to .items
        },
        link: (scope, elem) => {
            let scrolling = false;

            scope.newsScrollTimeout = scope.newsScrollTimeout || 1;
            scope.newsScrollMarkReadTimeout =
                scope.newsScrollMarkReadTimeout || 1;
            scope.newsScrollAutoPageWhenLeft =
                scope.newsScrollAutoPageWhenLeft || 50;
            scope.newsScrollItemsSelector =
                scope.newsScrollItemsSelector || '.items';


            elem.on('scroll', () => {

                // only allow one scrolling event to trigger
                if (!scrolling) {
                    scrolling = true;

                    $timeout(() => {
                        scrolling = false;
                    }, scope.newsScrollTimeout*1000);


                    let items = $(scope.newsScrollItemsSelector);

                    // autopaging
                    autoPage(!scope.newsScrollDisabledAutoPage,
                             scope.newsScrollAutoPageWhenLeft,
                             items, scope.newsScrollAutoPage);



                    // allow user to undo accidental scroll
                    $timeout(() => {
                        markRead(!scope.newsScrollDisabledMarkRead, items,
                                 scope.newsScrollMarkRead);
                    }, scope.newsScrollMarkReadTimeout*1000);
                }

            });

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', () => {
                elem.off('scroll');
            });
        }
    };
});