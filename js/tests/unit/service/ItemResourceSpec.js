/**
 * Nextcloud - News
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

    it('should filter out item duplicates', inject(function (ItemResource) {
        ItemResource.receive([{
            id: 3,
            fingerprint: 'a'
        }, {
            id: 4,
            fingerprint: 'a'
        }, {
            id: 2,
            fingerprint: 'b'
        }], 'items');
        expect(ItemResource.get(3).fingerprint).toBe('a');
        expect(ItemResource.get(2).fingerprint).toBe('b');
        expect(ItemResource.get(4)).toBe(undefined);
        expect(ItemResource.highestId).toBe(4);
        expect(ItemResource.lowestId).toBe(2);
    }));


    it('should receive the newestItemId', inject(function (ItemResource) {
        ItemResource.receive(2, 'starred');

        expect(ItemResource.getStarredCount()).toBe(2);
    }));


    it('should mark item as read', inject(function (ItemResource) {
        http.expectPOST('base/items/3/read', {isRead: true}).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                unread: true,
                fingerprint: 'a'
            },
            {
                id: 4,
                feedId: 3,
                unread: true,
                fingerprint: 'b'
            }
        ], 'items');

        ItemResource.markItemRead(3);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
    }));


    it('should mark multiple item as read', inject(function (ItemResource) {
        http.expectPOST('base/items/read/multiple', {
            itemIds: [3, 4]
        }).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                unread: true,
                fingerprint: 'a'
            },
            {
                id: 4,
                feedId: 3,
                unread: true,
                fingerprint: 'b'
            }
        ], 'items');

        ItemResource.markItemsRead([3, 4]);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(4).unread).toBe(false);
    }));


    it('should star item', inject(function (ItemResource) {
        http.expectPOST('base/items/4/a/star', {isStarred: true})
            .respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                starred: false,
                guidHash: 'a'
            },
            {
                id: 4,
                feedId: 3,
                fingerprint: 'b',
                starred: false
            }
        ], 'items');

        ItemResource.star(3);

        http.flush();

        expect(ItemResource.get(3).starred).toBe(true);
        expect(ItemResource.getStarredCount()).toBe(1);
    }));


    it('should mark feed as read', inject(function (ItemResource) {
        http.expectPOST('base/feeds/4/read', {
            highestItemId: 5
        }).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 4,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 5,
                feedId: 4,
                fingerprint: 'c',
                unread: true
            }
        ], 'items');
        ItemResource.receive(5, 'newestItemId');

        ItemResource.markFeedRead(4);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    it('should mark all as read', inject(function (ItemResource) {
        http.expectPOST('base/items/read', {
            highestItemId: 5
        }).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                fingerprint: 'c',
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


    it('toggle star', inject(function (ItemResource) {
        ItemResource.receive([
            {
                id: 3,
                fingerprint: 'a',
                starred: true
            },
            {
                id: 5,
                fingerprint: 'b',
                starred: false
            }
        ], 'items');

        ItemResource.star = jasmine.createSpy('star');

        ItemResource.toggleStar(3);
        expect(ItemResource.star).toHaveBeenCalledWith(3, false);

        ItemResource.toggleStar(5);
        expect(ItemResource.star).toHaveBeenCalledWith(5, true);
    }));


    it('should auto page newest first', inject(function (ItemResource) {
        http.expectGET(
            'base/items?id=4&limit=5&offset=3&oldestFirst=false&type=3')
            .respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                fingerprint: 'c',
                unread: true
            }
        ], 'items');

        ItemResource.autoPage(3, 4, false);

        http.flush();
    }));


    it('should auto page oldest first', inject(function (ItemResource) {
        http.expectGET(
            'base/items?id=4&limit=5&offset=5&oldestFirst=true&type=3')
            .respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                fingerprint: 'c',
                unread: true
            }
        ], 'items');

        ItemResource.autoPage(3, 4, true);

        http.flush();
    }));


    it('should auto page all', inject(function (ItemResource) {
        http.expectGET(
                'base/items?id=4&limit=5&offset=5&oldestFirst=true' +
                '&search=some+string&showAll=true&type=3')
            .respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                fingerprint: 'c',
                unread: true
            }
        ], 'items');

        ItemResource.autoPage(3, 4, true, true, 'some string');

        http.flush();
    }));


    it('should clear all state', inject(function (ItemResource) {
        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                fingerprint: 'a',
                unread: true
            },
            {
                id: 5,
                feedId: 3,
                fingerprint: 'b',
                unread: true
            },
            {
                id: 4,
                feedId: 4,
                fingerprint: 'c',
                unread: true
            }
        ], 'items');
        ItemResource.receive(5, 'newestItemId');
        ItemResource.receive(4, 'starred');

        ItemResource.clear();

        expect(ItemResource.size()).toBe(0);
        expect(ItemResource.highestId).toBe(0);
        expect(ItemResource.lowestId).toBe(0);
        expect(ItemResource.starredCount).toBe(0);
    }));


    it('should import articles', inject(function (ItemResource) {
        var json = 'test';

        http.expectPOST('base/feeds/import/articles', {
            json: json
        }).respond(200, {});

        ItemResource.importArticles(json);

        http.flush();

    }));


});