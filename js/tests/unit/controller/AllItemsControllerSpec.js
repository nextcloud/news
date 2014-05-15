/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('AllItemsController', function () {
    'use strict';

    var controller;

    beforeEach(module('News'));

    beforeEach(inject(function ($controller) {
        controller = $controller;
    }));


    it('should ', function () {
        expect(controller).toBeDefined();
    });

});