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

    var noInputFocused,
        noModifierKey,
        scrollArea,
        scrollToItem,
        scrollToNextItem,
        scrollToPreviousItem,
        toggleStar,
        toggleUnread,
        expandItem,
        openLink,
        getActiveItem;

    scrollArea = $('#app-content');

    noInputFocused = function (element) {
        return !(
            element.is('input')
            && element.is('select')
            && element.is('textarea')
            && element.is('checkbox')
        );
    };

    noModifierKey = function (event) {
        return !(
            event.shiftKey
            || event.altKey
            || event.ctrlKey
            || event.metaKey
        );
    };

    scrollToItem = function (item, scrollArea) {
        scrollArea.scrollTop(
            item.offset().top - scrollArea.offset().top + scrollArea.scrollTop()
        );
    };

    scrollToNextItem = function (scrollArea) {
        var items,
            counter,
            item;

        items = scrollArea.find('.feed_item');

        for (counter = 0; counter < items.length; counter += 1) {
            item = $(items[counter]);

            if (item.position().top > 1) {
                scrollToItem(scrollArea, item);
                return;
            }
        }

        // in case this is the last item it should still scroll below the top
        scrollArea.scrollTop(scrollArea.prop('scrollHeight'));

    };

    scrollToPreviousItem = function (scrollArea) {
        var items,
            item,
            counter,
            previous;

        items = scrollArea.find('.feed_item');

        for (counter = 0; counter < items.length; counter += 1) {
            item = $(items[counter]);

            if (item.position().top >= 0) {
                previous = item.prev();

                // if there are no items before the current one
                if (previous.length > 0) {
                    scrollToItem(scrollArea, previous);
                }

                return;
            }
        }

        // if there was no jump jump to the last element
        if (items.length > 0) {
            scrollToItem(scrollArea, items.last());
        }
    };

    getActiveItem = function (scrollArea) {
        var items,
            item,
            counter;

        items = scrollArea.find('.feed_item');

        for (counter = 0; counter < items.length; counter += 1) {
            item = $(items[counter]);

            // 130px of the item should be visible
            if ((item.height() + item.position().top) > 30) {
                return item;
            }
        }
    };

    toggleUnread = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.keep_unread').trigger('click');
    };

    toggleStar = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.item_utils .star').trigger('click');
    };

    expandItem = function (scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.item_heading a').trigger('click');
    };

    openLink = function (scrollArea) {
        var item = getActiveItem(scrollArea).find('.item_title a');
        item.trigger('click');  // mark read
        window.open(item.attr('href'), '_blank');
    };

    $(document).keyup(function (event) {
        var keyCode;

        keyCode = event.keyCode;


        if (noInputFocused($(':focus')) && noModifierKey(event)) {

            // j, n, right arrow
            if ([74, 78, 34].indexOf(keyCode) >= 0) {

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

}(window, document, jQuery));