/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */
NewsOnActive.$inject = ['$parse'];

export default function NewsOnActive($parse) {
    'use strict';
    return {
        restrict: 'A',
        link: function (scope, elem, attrs) {
            elem.on('set-active', function () {
                var callback = $parse(attrs.newsOnActive);
                scope.$apply(callback);
            });

        }
    };
};