/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsStopPropagation', () => {
	'use strict';
    return {
        restrict: 'A',
        link: (scope, element) => {
            element.bind('click', (e) => {
                e.stopPropagation();
            });
        }
    };
 });