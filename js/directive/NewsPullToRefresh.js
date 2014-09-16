/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsPullToRefresh', function ($route, $rootScope) {
    'use strict';

    var scrolled = false;

    return {
        restrict: 'A',
        scope: {
            'newsTimeout': '&'
        },
        link: function (scope, element) {

            // change in the route means the content is refreshed
            // so reset the var
            $rootScope.$on('$routeChangeStart', function () {
                scrolled = false;
            });

            element.on('scroll', function () {
                if (element.scrollTop() === 0 && scrolled) {
                    $route.reload();
                }
                scrolled = true;
            });
        }
    };
});