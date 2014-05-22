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

    return (scope, elm, attr) => {

        let file = elm[0].files[0];
        let reader = new FileReader();

        reader.onload = (event) => {
            elm[0].value = 0;
            scope.$fileContent = event.target.result;
            scope.$apply(attr.newsReadFile);  // FIXME: is there a more flexible
                                              // solution where we dont have to
                                              // bind the file to scope?
        };

        reader.reasAsText(file);
    };
});