/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsAutoFocus', function ($timeout) {
    'use strict';
    return function (scope, elem, attrs) {
        var toFocus = elem;

        if (attrs.newsAutoFocus) {
            toFocus = $(attrs.newsAutoFocus);
        }

        // to combat $digest already in process error when route changes
        $timeout(function () {
            toFocus.focus();
        }, 0);
    };
});