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

    var http;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
        $provide.constant('ITEM_BATCH_SIZE', 5);
    }));

    beforeEach(inject(function ($httpBackend) {
        http = $httpBackend;
    }));

    afterEach(function () {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


    it('should receive the newestItemId', inject(function (ItemResource) {
        ItemResource.receive(3, 'newestItemId');

        expect(ItemResource.getNewestItemId()).toBe(3);
    }));


    it('should receive the newestItemId', inject(function (ItemResource) {
        ItemResource.receive(2, 'starred');

        expect(ItemResource.getStarredCount()).toBe(2);
    }));


    it ('should mark item as read', inject(function (ItemResource) {
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


    it ('should mark multiple item as read', inject(function (ItemResource) {
        http.expectPOST('base/items/read/multiple', {
            itemIds: [3, 4]
        }).respond(200, {});

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

        ItemResource.markItemsRead([3, 4]);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(4).unread).toBe(false);
    }));



    it ('should star item', inject(function (ItemResource) {
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


    it ('should mark feed as read', inject(function (ItemResource) {
        http.expectPOST('base/feeds/4/read', {
            highestItemId: 5
        }).respond(200, {});

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
        ItemResource.receive(5, 'newestItemId');

        ItemResource.markFeedRead(4);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    it ('should mark all as read', inject(function (ItemResource) {
        http.expectPOST('base/items/read', {
            highestItemId: 5
        }).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                unread: true
            }
        ], 'items');
        ItemResource.receive(5, 'newestItemId');

        ItemResource.markRead();

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(4).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    it ('toggle star', inject(function (ItemResource) {
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


    it ('should auto page', inject(function (ItemResource) {
        http.expectGET('base/items?id=4&limit=5&offset=3&type=3')
            .respond(200, {});

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

        ItemResource.autoPage(3, 4);

        http.flush();
    }));


});