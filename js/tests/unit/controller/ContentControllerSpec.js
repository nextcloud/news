/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ContentController', () => {
    'use strict';

    let scope;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));

    beforeEach(inject(($rootScope) => {
        scope = $rootScope.$new();
    }));


    it('should publish data to models', inject(($controller, Publisher,
        FeedResource, ItemResource) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        Publisher.subscribe(FeedResource).toChannels('feeds');

        let controller = $controller('ContentController', {
            data: {
                'items': [
                    {id: 3},
                    {id: 4}
                ]
            },
            $scope: scope
        });

        expect(controller.getItems().length).toBe(2);
    }));


    it('should clear data on url change', inject(($controller,
        ItemResource) => {

        ItemResource.clear = jasmine.createSpy('clear');

        $controller('ContentController', {
            data: {},
            $scope: scope
        });

        expect(ItemResource.clear).toHaveBeenCalled();
    }));


    it('should return order by', inject(($controller,
        SettingsResource) => {

        $controller('ContentController', {
            SettingsResource: SettingsResource,
            $scope: scope,
            data: {},
        });

        expect(scope.Content.orderBy()).toBe('id');

        SettingsResource.set('oldestFirst', true);

        expect(scope.Content.orderBy()).toBe('-id');
    }));

});
