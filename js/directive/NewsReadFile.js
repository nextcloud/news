/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsReadFile', function () {
    'use strict';

    return function (scope, elem, attr) {

        elem.change(function () {

            var file = elem[0].files[0];
            var reader = new FileReader();

            reader.onload = function (event) {
                // FIXME: is there a more flexible solution where we dont have
                // to bind the file to scope?
                scope.$fileContent = event.target.result;
                scope.$apply(attr.newsReadFile);
            };

            reader.readAsText(file);
        });
    };
});