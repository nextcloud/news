/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
export default /* @ngInject */ function () {
    'use strict';
    var shown = false;
    return {
        restrict: 'E',
        link: function (scope, elem) {
            elem.hide();
            if (!shown) {
                shown = true;
                var notification = elem.html();
                OC.Notification.showHtml(notification);
            }
        }
    };
}