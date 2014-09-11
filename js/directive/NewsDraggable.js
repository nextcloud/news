/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsDraggable', function () {
    'use strict';

    return function (scope, elem, attr) {
        var options = scope.$eval(attr.newsDraggable);

        if (angular.isDefined(options)) {
            elem.draggable(options);
        } else {
            elem.draggable();
        }
    };
});