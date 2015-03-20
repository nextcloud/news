/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsSearch', function ($timeout) {
    'use strict';

    var timer;

    return {
        restrict: 'E',
        scope: {
            'onSearch': '='
        },
        link: function (scope) {
            $('#searchbox').on('search keyup', function () {
                var value = $(this).val();
                if (timer) {
                    $timeout.cancel(timer);
                }

                timer = $timeout(function () {
                    scope.$apply(function () {
                        scope.onSearch(value);
                    });
                }, 500);
            });
        }
    };
});