/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ExploreController', function () {
    'use strict';

    var controller,
        scope,
        sites;

    beforeEach(module('News'));

    beforeEach(inject(function ($controller, $rootScope) {
        scope = $rootScope.$new();
        sites = {
            data: 'hi'
        };

        controller = $controller('ExploreController', {
            $rootScope: scope,
            sites: sites
        });
    }));


    it('should expose sites', inject(function () {
        expect(controller.sites).toBe(sites);
    }));


    it('should broadcast add feed', inject(function () {
        scope.$broadcast = jasmine.createSpy('broadcast');

        controller.subscribeTo('test');
        expect(scope.$broadcast).toHaveBeenCalledWith('addFeed', 'test');
    }));

});