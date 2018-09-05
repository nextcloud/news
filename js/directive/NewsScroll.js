/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsScroll', function ($timeout, ITEM_AUTO_PAGE_SIZE,
    MARK_READ_TIMEOUT, SCROLL_TIMEOUT) {
    'use strict';
    var timer;

    // autopaging
    var autoPage = function (limit, elem, scope) {
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
            if (item[0].getBoundingClientRect().top < 0) {
                scope.$apply(scope.newsScrollAutoPage);
                break;
            }

            counter += 1;
        }
    };

    // mark read
    var markRead = function (enabled, elem, scope) {
        if (enabled) {
            var ids = [];
            var articles = elem.querySelectorAll('.item:not(.read)');

            articles.forEach(function(article) {
                // distance to top + height
                var distTop = article.offsetTop + article.offsetHeight;
                var scrollTop = window.pageYOffset ||
                    document.documentElement.scrollTop;
                if (distTop < scrollTop) {
                    ids.push(parseInt(article.dataset.id, 10));
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
            'newsScroll': '@',
            'newsScrollAutoPage': '&',
            'newsScrollMarkRead': '&',
            'newsScrollEnabledMarkRead': '=',
        },
        link: function (scope, elem) {
            var allowScroll = true;

            var scrollHandler = function () {
                // allow only one scroll event to trigger every 300ms
                if (allowScroll) {
                    allowScroll = false;

                    $timeout(function () {
                        allowScroll = true;
                    }, SCROLL_TIMEOUT * 1000);

                    autoPage(ITEM_AUTO_PAGE_SIZE, elem, scope);

                    // dont stack mark read requests
                    if (timer) {
                        $timeout.cancel(timer);
                    }

                    // allow user to undo accidental scroll
                    timer = $timeout(function () {
                        markRead(scope.newsScrollEnabledMarkRead,
                                 elem[0],
                                 scope);
                        timer = undefined;
                    }, MARK_READ_TIMEOUT*1000);
                }
            };

            $(document).on('scroll', scrollHandler);

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', function () {
                $(document).off('scroll', scrollHandler);
            });
        }
    };
});