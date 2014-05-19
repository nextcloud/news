/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('Item', function () {
    'use strict';

    beforeEach(module('News'));


    it('should receive the newestItemId', inject(function (Item) {
        Item.receive(3, 'newestItemId');

        expect(Item.getNewestItemId()).toBe(3);
    }));


    it('should receive the newestItemId', inject(function (Item) {
        Item.receive(2, 'starred');

        expect(Item.getStarredCount()).toBe(2);
    }));


    it('should receive items', inject(function (Item) {
        Item.receive([
            {
                id: 3
            },
            {
                id: 4
            }
        ], 'items');

        expect(Item.size()).toBe(2);
    }));

});