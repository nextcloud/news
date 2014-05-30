/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsAudio', () => {
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
        link: (scope, elm) => {
            let source = elm.children().children('source')[0];
            let cantPlay = false;
            source.addEventListener('error', () =>  {
                scope.$apply(() => {
                    cantPlay = true;
                });
            });

            scope.cantPlay = () => {
                return cantPlay;
            };
        }
    };
});