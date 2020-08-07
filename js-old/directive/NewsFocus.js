/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsFocus', function ($timeout, $interpolate) {
    'use strict';

    return function (scope, elem, attrs) {
        elem.click(function () {
            var toReadd = $($interpolate(attrs.newsFocus)(scope));
            $timeout(function () {
                toReadd.focus();
            }, 500);
        });
    };

});