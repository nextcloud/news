/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
app.run(function ($rootScope, $location, Loading) {
    'use strict';

    $rootScope.$on('$routeChangeStart', function () {
        Loading.isActive = true;
    });

    $rootScope.$on('$routeChangeSuccess', function () {
        Loading.isActive = false;
    });

    // in case of wrong id etc show all items
    $rootScope.$on('$routeChangeError', function () {
        $location.path('/items');
    });
});