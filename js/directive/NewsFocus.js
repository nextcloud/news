/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsFocus', ($timeout, $interpolate) => {
    'use strict';

    return (scope, elem, attrs) => {
        elem.click(() => {
            let toReadd = $($interpolate(attrs.newsFocus)(scope));
            $timeout(() => {
                toReadd.focus();
            }, 500);
        });
    };

});