/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsReadFile', () => {
    'use strict';

    return (scope, elem, attr) => {

        elem.change(() => {

            let file = elem[0].files[0];
            let reader = new FileReader();

            reader.onload = (event) => {
                elem[0].value = 0;
                // FIXME: is there a more flexible solution where we dont have
                // to bind the file to scope?
                scope.$fileContent = event.target.result;
                scope.$apply(attr.newsReadFile);
            };

            reader.readAsText(file);
        });
    };
});