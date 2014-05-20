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
(function (document, $) {
    'use strict';

    $(document).keyup(function (event) {
        var keyCode,
            noInputFocused,
            noModifierKey,
            scrollArea,
            jumpToNextItem,
            jumpToPreviousItem,
            toggleStar,
            toggleUnread,
            expandItem,
            openLink,
            getActiveItem;

        keyCode = event.keyCode;
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

        if (noInputFocused($(':focus')) && noModifierKey(event)) {

            // j, n, right arrow
            if ([74, 78, 34].indexOf(keyCode) >= 0) {

                event.preventDefault();
                jumpToNextItem(scrollArea);

            // k, p, left arrow
            } else if ([75, 80, 37].indexOf(keyCode) >= 0) {

                event.preventDefault();
                jumpToPreviousItem(scrollArea);

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
                jumpToNextItem(scrollArea);

            // o
            } else if ([79].indexOf(keyCode) >= 0) {

                event.preventDefault();
                openLink(scrollArea);

            }

        }
    });

}(document, jQuery));