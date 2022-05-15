/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTriggerClick', function () {
    'use strict';

    return function (scope, elm, attr) {
        elm.click(function () {
            $(attr.newsTriggerClick).trigger('click');
        });
    };

});