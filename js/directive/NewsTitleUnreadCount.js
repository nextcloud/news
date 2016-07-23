/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTitleUnreadCount', function ($window) {
    'use strict';

    var baseTitle = $window.document.title;
    var titles = baseTitle.split('-');
    var appName = titles[0] || 'News';
    var ownCloudName = titles[1] || 'Nextcloud';

    return {
        restrict: 'E',
        scope: {
            unreadCount: '@'
        },
        link: function (scope, elem, attrs) {
            attrs.$observe('unreadCount', function (value) {
                if (value !== '0') {
                    $window.document.title = appName +
                        '(' + value + ') - ' + ownCloudName;
                } else {
                    $window.document.title = appName + ' - ' + ownCloudName;
                }
            });
        }
    };

});