/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsSlideUp', ($rootScope, $document) => {
    'use strict';

    return (scope, elem, attr) => {
        // defaults
        let slideArea = elem;
        let cssClass = false;

        let options = scope.$eval(attr.newsSlideUp);

        if (options) {
            if (options.selector) {
                slideArea = $(options.selector);
            }

            if (options.cssClass) {
                cssClass = options.cssClass;
            }


            if (options.hideOnFocusLost) {
                $($document[0].body).click(() => {
                    $rootScope.$broadcast('newsSlideUp');
                });

                $rootScope.$on('newsSlideUp', (scope, params) => {
                    if (params !== slideArea &&
                        slideArea.is(':visible') &&
                        !slideArea.is(':animated')) {

                        slideArea.slideUp();

                        if (cssClass) {
                            elem.removeClass(cssClass);
                        }
                    }
                });

                slideArea.click((event) => {
                    $rootScope.$broadcast('newsSlideUp', slideArea);
                    event.stopPropagation();
                });

                elem.click((event) => {
                    $rootScope.$broadcast('newsSlideUp', slideArea);
                    event.stopPropagation();
                });
            }
        }

        elem.click(() => {
            if (slideArea.is(':visible') && !slideArea.is(':animated')) {
                slideArea.slideUp();
                if (cssClass) {
                    elem.removeClass(cssClass);
                }
            } else {
                slideArea.slideDown();
                if (cssClass) {
                    elem.addClass(cssClass);
                }
            }
        });

    };
});