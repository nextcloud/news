/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTimeout', ($timeout) => {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            'newsTimeout': '&'
        },
        link: (scope) => {
            let seconds = 7;
            let timer = $timeout(scope.newsTimeout, seconds * 1000);

            // remove timeout if element is being removed by
            // for instance clicking on the x button
            scope.$on('$destroy', () => {
                $timeout.cancel(timer);
            });
        }
    };
});