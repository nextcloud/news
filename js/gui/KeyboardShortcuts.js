/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

/**
 * Code in here acts only as a click shortcut mechanism. That's why its not
 * being put into a directive since it has to be tested with protractor
 * anyways and theres no benefit from wiring it into the angular app
 */
(function (window, document, $) {
    'use strict';

    var noInputFocused = function (element) {
        return !(
            element.is('input') ||
            element.is('select') ||
            element.is('textarea') ||
            element.is('checkbox')
        );
    };

    var noModifierKey = function (event) {
        return !(
            event.shiftKey ||
            event.altKey ||
            event.ctrlKey ||
            event.metaKey
        );
    };

    var reloadFeed = function (navigationArea) {
        navigationArea.find('.active > a:visible').trigger('click');
    };


    var nextFeed = function (navigationArea) {
        var current = navigationArea.find('.active');
        var elements = navigationArea.find('.subscriptions:visible,' +
                                           '.starred:visible,' +
                                           '.feed:visible');

        if (current.hasClass('folder')) {
            while (current.length > 0) {
                var subfeeds = current.find('.feed:visible');
                if (subfeeds.length > 0) {
                    $(subfeeds[0]).children('a:visible').trigger('click');
                    return;
                }
                current = current.next('.folder');
            }

            // no subfeed found
            return;
        }

        // FIXME: O(n) runtime. If someone creates a nice and not fugly solution
        // please create a PR
        for (var i=0; i<elements.length-1; i+=1) {
            var element = elements[i];

            if (element === current[0]) {
                var next = elements[i+1];
                $(next).children('a:visible').trigger('click');
                break;
            }
        }
    };

    var previousFeed = function (navigationArea) {
        var current = navigationArea.find('.active');
        var elements = navigationArea.find('.subscriptions:visible,' +
                                           '.starred:visible,' +
                                           '.feed:visible');

        if (current.hasClass('folder')) {
            current = current.prev('.folder');
            while (current.length > 0) {
                var subfeeds = current.find('.feed:visible');
                if (subfeeds.length > 0) {
                    $(subfeeds[subfeeds.length-1])
                        .children('a:visible').trigger('click');
                    return;
                }
            }

            // no subfeed found
            var starred = $('.starred:visible');
            if (starred.length > 0) {
                starred.children('a:visible').trigger('click');
            }

            return;
        }

        // FIXME: O(n) runtime. If someone creates a nice and not fugly solution
        // please create a PR
        for (var i=elements.length-1; i>0; i-=1) {
            var element = elements[i];

            if (element === current[0]) {
                var previous = elements[i-1];
                $(previous).children('a:visible').trigger('click');
                break;
            }
        }
    };

    var onActiveItem = function (scrollArea, callback) {
        var items = scrollArea.find('.item');

        items.each(function (index, item) {
            item = $(item);

            // 130px of the item should be visible
            if ((item.height() + item.position().top) > 30) {
                callback(item);

                return false;
            }
        });

    };

    var toggleUnread = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.toggle-keep-unread').trigger('click');
        });
    };

    var toggleStar = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.star').trigger('click');
        });
    };

    var expandItem = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.utils').trigger('click');
        });
    };

    var openLink = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.trigger('click');  // mark read
            window.open(item.find('.external:visible').attr('href'), '_blank');
        });
    };

    var scrollToItem = function (scrollArea, item, isCompactMode) {
        // if you go to the next article in compact view, it should
        // expand the current one
        scrollArea.scrollTop(
            item.offset().top - scrollArea.offset().top + scrollArea.scrollTop()
        );

        if (isCompactMode) {
            onActiveItem(scrollArea, function (item) {
                if (!item.hasClass('open')) {
                    item.find('.utils').trigger('click');
                }
            });
        }
    };

    var scrollToNextItem = function (scrollArea, isCompactMode) {
        var items = scrollArea.find('.item');
        var jumped = false;

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top > 1) {
                scrollToItem(scrollArea, item, isCompactMode);

                jumped = true;

                return false;
            }
        });

        // in case this is the last item it should still scroll below the top
        if (!jumped) {
            scrollArea.scrollTop(scrollArea.prop('scrollHeight'));
        }

    };

    var scrollToPreviousItem = function (scrollArea, isCompactMode) {
        var items = scrollArea.find('.item');
        var jumped = false;

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top >= 0) {
                var previous = item.prev();

                // if there are no items before the current one
                if (previous.length > 0) {
                    scrollToItem(scrollArea, previous, isCompactMode);
                }

                jumped = true;

                return false;
            }
        });

        // if there was no jump jump to the last element
        if (!jumped && items.length > 0) {
            scrollToItem(scrollArea, items.last());
        }

    };


    $(document).keyup(function (event) {
        if (noInputFocused($(':focus')) && noModifierKey(event)) {
            var keyCode = event.keyCode;
            var scrollArea = $('#app-content');
            var navigationArea = $('#app-navigation');
            var isCompactMode = $('#app-content-wrapper > .compact').length > 0;

            // j, n, right arrow
            if ([74, 78, 39].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToNextItem(scrollArea, isCompactMode);

            // k, p, left arrow
            } else if ([75, 80, 37].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToPreviousItem(scrollArea, isCompactMode);

            // u
            } else if ([85].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleUnread(scrollArea);

            // e
            } else if ([69].indexOf(keyCode) >= 0) {

                event.preventDefault();
                expandItem(scrollArea);

            // s, i, l
            } else if ([73, 83, 76].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleStar(scrollArea);

            // h
            } else if ([72].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleStar(scrollArea);
                scrollToNextItem(scrollArea);

            // o
            } else if ([79].indexOf(keyCode) >= 0) {

                event.preventDefault();
                openLink(scrollArea);

            // r
            } else if ([82].indexOf(keyCode) >= 0) {

                event.preventDefault();
                reloadFeed(navigationArea);

            // f
            } else if ([70].indexOf(keyCode) >= 0) {

                event.preventDefault();
                nextFeed(navigationArea);

            // d
            } else if ([68].indexOf(keyCode) >= 0) {

                event.preventDefault();
                previousFeed(navigationArea);

            }

        }
    });

}(window, document, $));