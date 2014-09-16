/**
 * ownCloud - News
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

    return {
        restrict: 'E',
        scope: {
            unreadCount: '@'
        },
        link: function (scope, elem, attrs) {
            attrs.$observe('unreadCount', function (value) {
                var titles = baseTitle.split('-');

                if (value !== '0') {
                    $window.document.title = titles[0] +
                        '(' + value + ') - ' + titles[1];
                }
            });
        }
    };

});