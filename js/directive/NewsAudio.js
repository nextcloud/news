/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsAudio', function () {
    'use strict';
    return {
        restrict: 'E',
        scope: {
            src: '@',
            type: '@'
        },
        transclude: true,
        template: '' +
        '<audio controls="controls" preload="none" ng-hide="cantPlay()">' +
            '<source ng-src="{{ src|trustUrl }}">' +
        '</audio>' +
        '<a ng-href="{{ src|trustUrl }}" class="button" ng-show="cantPlay()" ' +
            'ng-transclude></a>',
        link: function (scope, elm) {
            var source = elm.children().children('source')[0];
            var cantPlay = false;

            source.addEventListener('error', function () {
                scope.$apply(function () {
                    cantPlay = true;
                });
            });

            scope.cantPlay = function () {
                return cantPlay;
            };
        }
    };
});