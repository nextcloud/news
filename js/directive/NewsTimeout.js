/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsTimeout', function ($timeout, $rootScope) {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            'newsTimeout': '&'
        },
        link: function (scope, element) {
            var destroyed = false;
            var seconds = 7;
            var timer = $timeout(scope.newsTimeout, seconds * 1000);

            // remove timeout if element is being removed by
            // for instance clicking on the x button
            scope.$on('$destroy', function () {
                destroyed = true;
                $timeout.cancel(timer);
            });

            // also delete the entry if undo is ignored and the url
            // is changed
            $rootScope.$on('$locationChangeStart', function () {
                // $locationChangeStart triggers twice because of the trailing
                // slash on the link which is kinda a hack to reload the route
                // if you click on the link when the route is the same
                $timeout.cancel(timer);
                if (!destroyed) {
                    destroyed = true;
                    element.remove();
                    scope.newsTimeout();
                }
            });
        }
    };
});