/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsEnclosure', function () {
    'use strict';
    return {
        restrict: 'E',
        scope: {
            link: '@',
            type: '@'
        },
        transclude: true,
        template: '<div>' +
            '<video controls preload="none" ' +
                'ng-show="mediaType==\'video\' && !cantPlay()">' +
                '<source ng-src="{{ link|trustUrl }}" type="{{ type }}">' +
            '</video>' +
            '<audio controls preload="none" ' +
                'ng-show="mediaType==\'audio\' && !cantPlay()">' +
                '<source ng-src="{{ link|trustUrl }}" type="{{ type }}">' +
            '</audio>' +
            '<div ng-transclude ng-show="cantPlay()"></div>' +
        '</div>',
        link: function (scope, elem) {
            if (scope.type.indexOf('audio') === 0) {
                scope.mediaType = 'audio';
            } else {
                scope.mediaType = 'video';
            }
            var source = elem.children()
                .children(scope.mediaType)
                .children('source')[0];

            var cantPlay = false;

            scope.cantPlay = function () {
                return cantPlay;
            };

            source.addEventListener('error', function () {
                scope.$apply(function () {
                    cantPlay = true;
                });
            });
        }
    };
});