/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsStickyMenu', function (NC_MAJOR_VERSION) {
    'use strict';

    return function (scope, elem, attr) {
        var height = 40;

        $(attr.newsStickyMenu).scroll(function () {
            var scrollHeight = $(this).scrollTop();

            if (scrollHeight > height) {
                elem.addClass('fixed');
                if (NC_MAJOR_VERSION < 25) {
                    elem.css('top', scrollHeight);
                }
            } else {
                elem.removeClass('fixed');
            }
        });
    };
});