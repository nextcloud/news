/**
* Nextcloud - News
*
* This file is licensed under the Affero General Public License version 3 or
* later. See the COPYING file.
*
* @author Marco Nassabain <marco.nassabain@hotmail.com>
* @author Nicolas Wendling <nicolas.wendling1011@gmail.com>
* @author Jimmy Huynh <natorisaki@gmail.com>
* @author Aurélien David <dav.aurelien@gmail.com>
*/
app.directive('clickOutside', function ($document) {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            clickOutside: '&'
        },
        link: function (scope, el) {

            $document.on('click', function (e) {
                if (el !== e.target && !el[0].contains(e.target)) {
                    scope.$apply(function () {
                        scope.$eval(scope.clickOutside);
                    });
                }
            });
        }
    };
});
