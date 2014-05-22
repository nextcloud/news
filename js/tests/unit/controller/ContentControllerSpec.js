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

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    it('should publish data to models', inject(($controller, Publisher,
        FeedResource, ItemResource) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        Publisher.subscribe(FeedResource).toChannels('feeds');

        let controller = $controller('ContentController', {
            data: {
                'items': [
                    {
                        id: 3
                    },
                    {
                        id: 4
                    }
                ],
                'feeds': [
                    {
                        url: 'hi'
                    }
                ]
            }
        });

        expect(controller.getItems().length).toBe(2);
        expect(controller.getFeeds().length).toBe(1);
    }));


    it('should clear data on url change', inject(($controller,
        ItemResource) => {

        ItemResource.clear = jasmine.createSpy('clear');

        $controller('ContentController', {
            data: {}
        });

        expect(ItemResource.clear).toHaveBeenCalled();
    }));

});
