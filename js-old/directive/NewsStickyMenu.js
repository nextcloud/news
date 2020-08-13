/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsStickyMenu', function () {
    'use strict';

    return function (scope, elem, attr) {
        var height = 40;

        $(attr.newsStickyMenu).scroll(function () {
            var scrollHeight = $(this).scrollTop();

            if (scrollHeight > height) {
                elem.addClass('fixed');
                elem.css('top', scrollHeight);
            } else {
                elem.removeClass('fixed');
            }
        });
    };
});