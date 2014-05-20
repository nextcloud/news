/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ItemResource', function () {
    'use strict';

    beforeEach(module('News'));


    it('should receive the newestItemId', inject(function (ItemResource) {
        ItemResource.receive(3, 'newestItemId');

        expect(ItemResource.getNewestItemId()).toBe(3);
    }));


    it('should receive the newestItemId', inject(function (ItemResource) {
        ItemResource.receive(2, 'starred');

        expect(ItemResource.getStarredCount()).toBe(2);
    }));


    it('should receive items', inject(function (ItemResource) {
        ItemResource.receive([
            {
                id: 3
            },
            {
                id: 4
            }
        ], 'items');

        expect(ItemResource.size()).toBe(2);
    }));

});