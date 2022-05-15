/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsSearch', function ($document, $location) {
    'use strict';

    return {
        restrict: 'E',
        scope: {
            'onSearch': '='
        },
        link: function (scope) {
            var box = $('#searchbox');
            box.val($location.search().search);

            var doSearch = function () {
                var value = box.val();
                scope.$apply(function () {
                    scope.onSearch(value);
                });
            };

            box.on('search keydown', function (event) {
                if (event.type === 'search' || event.keyCode === 13) {
                    event.preventDefault();
                    doSearch();
                }
            });

            // carry over search on route change
            scope.$watch(function () {
                return $location.search();
            }, function (search) {
                if (search && search.search) {
                    box.val(search.search);
                } else {
                    box.val('');
                }
            });
        }
    };
});
