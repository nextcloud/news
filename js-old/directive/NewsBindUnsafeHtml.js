/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsBindHtmlUnsafe', function () {
    'use strict';

    return function (scope, elem, attr) {
        scope.$watch(attr.newsBindHtmlUnsafe, function () {
            elem.html(scope.$eval(attr.newsBindHtmlUnsafe));
        });
    };
});