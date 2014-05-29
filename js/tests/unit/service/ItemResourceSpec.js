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

    let http;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));

    beforeEach(inject(($httpBackend) => {
        http = $httpBackend;
    }));


    it('should receive the newestItemId', inject((ItemResource) => {
        ItemResource.receive(3, 'newestItemId');

        expect(ItemResource.getNewestItemId()).toBe(3);
    }));


    it('should receive the newestItemId', inject((ItemResource) => {
        ItemResource.receive(2, 'starred');

        expect(ItemResource.getStarredCount()).toBe(2);
    }));


    it ('should mark item as read', inject((ItemResource) => {
        http.expectPOST('base/items/3/read', {isRead: true}).respond(200, {});

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

        ItemResource.markItemRead(3);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
    }));


    it ('should star item', inject((ItemResource) => {
        http.expectPOST('base/items/4/a/star', {isStarred: true})
            .respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                starred: false,
                guidHash: 'a'
            },
            {
                id: 4,
                feedId: 3,
                starred: false
            }
        ], 'items');

        ItemResource.star(3);

        http.flush();

        expect(ItemResource.get(3).starred).toBe(true);
        expect(ItemResource.getStarredCount()).toBe(1);
    }));


    it ('should mark feed as read', inject((ItemResource) => {
        http.expectPOST('base/feeds/4/read').respond(200, {});

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
            },
            {
                id: 5,
                feedId: 4,
                unread: true
            }
        ], 'items');

        ItemResource.markFeedRead(4);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    it ('should mark all as read', inject((ItemResource) => {
        http.expectPOST('base/items/read').respond(200, {});

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
            },
            {
                id: 5,
                feedId: 4,
                unread: true
            }
        ], 'items');

        ItemResource.markRead();

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(4).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    it ('toggle star', inject((ItemResource) => {
        ItemResource.receive([
            {
                id: 3,
                starred: true
            },
            {
                id: 5,
                starred: false
            }
        ], 'items');

        ItemResource.star = jasmine.createSpy('star');

        ItemResource.toggleStar(3);
        expect(ItemResource.star).toHaveBeenCalledWith(3, false);

        ItemResource.toggleStar(5);
        expect(ItemResource.star).toHaveBeenCalledWith(5, true);
    }));


    afterEach(() => {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


});