/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.filter('unreadCountFormatter', function () {
    'use strict';

    return function (unreadCount) {
        if (unreadCount > 999) {
            return '999+';
        }
        return unreadCount;
    };
});