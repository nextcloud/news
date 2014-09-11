/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTimeout', function ($timeout) {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            'newsTimeout': '&'
        },
        link: function (scope) {
            var seconds = 7;
            var timer = $timeout(scope.newsTimeout, seconds * 1000);

            // remove timeout if element is being removed by
            // for instance clicking on the x button
            scope.$on('$destroy', function () {
                $timeout.cancel(timer);
            });
        }
    };
});