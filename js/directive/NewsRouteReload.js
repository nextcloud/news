/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsRouteReload', ($location, $route) => {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            'ngHref': '@'
        },
        link: (scope, elem, attrs) => {
            elem.click(() => {
                if ($location.path() === attrs.ngHref.substring(1)) {
                    $route.reload();
                }
            });
        }
    };
});