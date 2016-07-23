/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

/**
 * Pause playback on elements other than the current one
 */
app.directive('newsPlayOne', function ($rootScope) {
    'use strict';
    return {
        restrict: 'A',
        link: function (scope, elem) {
            elem.on('play', function () {
                $rootScope.$broadcast('playing', elem);
            });

            $rootScope.$on('playing', function (scope, args) {
                if (args[0] !== elem[0]) {
                    elem[0].pause();
                }
            });
        }
    };
});