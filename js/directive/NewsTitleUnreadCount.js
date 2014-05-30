/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTitleUnreadCount', ($window) => {
    'use strict';

    let baseTitle = $window.document.title;

    return {
        restrict: 'E',
        scope: {
            unreadCount: '@'
        },
        link: (scope, elem, attrs) => {
            attrs.$observe('unreadCount', (value) => {
                let titles = baseTitle.split('-');

                if (value !== '0') {
                    $window.document.title = titles[0] +
                        '(' + value + ') - ' + titles[1];
                }
            });
        }
    };

});