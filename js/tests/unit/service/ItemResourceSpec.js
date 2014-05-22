/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ItemResource', () => {
    'use strict';

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    it('should receive the newestItemId', inject((ItemResource) => {
        ItemResource.receive(3, 'newestItemId');

        expect(ItemResource.getNewestItemId()).toBe(3);
    }));


    it('should receive the newestItemId', inject((ItemResource) => {
        ItemResource.receive(2, 'starred');

        expect(ItemResource.getStarredCount()).toBe(2);
    }));


    it('should receive items', inject((ItemResource) => {
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


    it ('should mark item as read', inject((ItemResource) => {
        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                unread: true
            },
            {
                id: 4,
                feedId: 3,
                unread: true
            }
        ], 'items');

        ItemResource.markRead(3);

        expect(ItemResource.get(3).unread).toBe(false);
    }));

});