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

    var scrollToItem = function (item, scrollArea) {
        scrollArea.scrollTop(
            item.offset().top - scrollArea.offset().top + scrollArea.scrollTop()
        );
    };

    var scrollToNextItem = function (scrollArea) {
        var items = scrollArea.find('.item');

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top > 1) {
                scrollToItem(scrollArea, item);
                return false;
            }
        });

        // in case this is the last item it should still scroll below the top
        scrollArea.scrollTop(scrollArea.prop('scrollHeight'));

    };

    var scrollToPreviousItem = function (scrollArea) {
        var items = scrollArea.find('.item');

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top >= 0) {
                var previous = item.prev();

                // if there are no items before the current one
                if (previous.length > 0) {
                    scrollToItem(scrollArea, previous);
                }

                return false;
            }
        });

        // if there was no jump jump to the last element
        if (items.length > 0) {
            scrollToItem(scrollArea, items.last());
        }
    };

    var getActiveItem = function (scrollArea) {
        var items = scrollArea.find('.item');

        items.each(function (index, item) {
            item = $(item);

            // 130px of the item should be visible
            if ((item.height() + item.position().top) > 30) {
                return item;
            }
        });
    };

    var toggleUnread = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.keep-unread').trigger('click');
    };

    var toggleStar = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.star').trigger('click');
    };

    var expandItem = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.title').trigger('click');
    };

    var openLink = function (scrollArea) {
        var item = getActiveItem(scrollArea).find('.title a');
        item.trigger('click');  // mark read
        window.open(item.attr('href'), '_blank');
    };

    $(document).keyup(function (event) {
        if (noInputFocused($(':focus')) && noModifierKey(event)) {
            var keyCode = event.keyCode;
            var scrollArea = $('#app-content');

            // j, n, right arrow
            if ([74, 78, 33].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToNextItem(scrollArea);

            // k, p, left arrow
            } else if ([75, 80, 37].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToPreviousItem(scrollArea);

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

            }

        }
    });

}(window, document, $));