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


    it ('should keep item unread', inject((ItemResource) => {
        http.expectPOST('base/items/3/read', {isRead: false}).respond(200, {});

        ItemResource.receive([
            {
                id: 3,
                feedId: 4,
                unread: false
            },
            {
                id: 4,
                feedId: 3,
                unread: false
            }
        ], 'items');

        ItemResource.keepUnread(3);

        http.flush();

        expect(ItemResource.get(3).keepUnread).toBe(true);
        expect(ItemResource.get(3).unread).toBe(true);
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

        ItemResource.read(3);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
    }));


    it ('should star item', inject((ItemResource) => {
        http.expectPOST('base/items/4/a/star', {isStarred: true}).respond(200, {});

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

        ItemResource.readFeed(4);

        http.flush();

        expect(ItemResource.get(3).unread).toBe(false);
        expect(ItemResource.get(5).unread).toBe(false);
    }));


    afterEach(() => {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


});