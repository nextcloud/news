/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsToggleShow', function () {
    'use strict';
    return {
        restrict: 'A',
        scope: {
            'newsToggleShow': '@'
        },
        link: function (scope, elem) {
            elem.click(function () {
                var target = $(scope.newsToggleShow);
                target.toggle();
            });
        }
    };
});
