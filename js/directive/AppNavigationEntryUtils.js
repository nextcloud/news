/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.run(($document, $rootScope) => {
    'use strict';
    $document.click((event) => {
        $rootScope.$broadcast('documentClicked', event);
    });
});

app.directive('appNavigationEntryUtils', () => {
    'use strict';
    return {
        restrict: 'C',
        link: (scope, elm) => {
            let menu = elm.siblings('.app-navigation-entry-menu');
            let button = $(elm)
                .find('.app-navigation-entry-utils-menu-button button');

            button.click(() => {
                menu.toggle();
            });

            scope.$on('documentClicked', (scope, event) => {
                if (event.target !== button[0]) {
                    menu.hide();
                }
            });
        }
    };
});