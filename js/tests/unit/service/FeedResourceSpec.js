/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('FeedResource', () => {
    'use strict';

    let resource,
        http;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    beforeEach(inject((FeedResource, $httpBackend) => {
        resource = FeedResource;
        http = $httpBackend;
        FeedResource.receive([
            {id: 1, folderId: 3, url: 'ye', unreadCount: 45},
            {id: 2, folderId: 4, url: 'sye', unreadCount: 25},
            {id: 3, folderId: 3, title: 'hore', url: '1sye', unreadCount: 0}
        ]);
    }));

    it('should mark all read', inject((FeedResource) => {

        FeedResource.markRead();

        expect(FeedResource.getUnreadCount()).toBe(0);
    }));

    it('should mark a feed read', inject((FeedResource) => {

        FeedResource.markFeedRead(1);

        expect(FeedResource.get('ye').unreadCount).toBe(0);
    }));


    it('should mark an item read', inject((FeedResource) => {

        FeedResource.markItemOfFeedRead(1);

        expect(FeedResource.get('ye').unreadCount).toBe(44);
    }));

    it('should mark an item unread', inject((FeedResource) => {

        FeedResource.markItemOfFeedUnread(1);

        expect(FeedResource.get('ye').unreadCount).toBe(46);
    }));


    it('should get all of folder', inject((FeedResource) => {

        let folders = FeedResource.getByFolderId(3);

        expect(folders.length).toBe(2);
    }));



    it('should cache unreadcount', inject((FeedResource) => {
        expect(FeedResource.getUnreadCount()).toBe(70);

        FeedResource.markItemOfFeedRead(3);
        expect(FeedResource.getUnreadCount()).toBe(69);

        FeedResource.markItemOfFeedUnread(3);
        expect(FeedResource.getUnreadCount()).toBe(70);

        FeedResource.markFolderRead(3);
        expect(FeedResource.getUnreadCount()).toBe(25);

        FeedResource.markRead();
        expect(FeedResource.getUnreadCount()).toBe(0);
    }));


    it('should cache folder unreadcount', inject((FeedResource) => {
        expect(FeedResource.getFolderUnreadCount(3)).toBe(45);

        FeedResource.markItemOfFeedRead(3);
        expect(FeedResource.getFolderUnreadCount(3)).toBe(44);

        FeedResource.markItemOfFeedUnread(3);
        expect(FeedResource.getFolderUnreadCount(3)).toBe(45);

        FeedResource.markFolderRead(3);
        expect(FeedResource.getFolderUnreadCount(3)).toBe(0);

        FeedResource.markRead();
        expect(FeedResource.getFolderUnreadCount(4)).toBe(0);
    }));


    it('should cache unreadcount', inject((FeedResource) => {
        FeedResource.markItemsOfFeedsRead([1, 2]);
        expect(FeedResource.getUnreadCount()).toBe(68);
    }));



    it ('should delete a feed', inject((FeedResource) => {
        http.expectDELETE('base/feeds/1').respond(200, {});

        FeedResource.delete('ye');

        http.flush();

        expect(FeedResource.size()).toBe(2);
    }));


    it ('should rename a feed', inject((FeedResource) => {
        http.expectPOST('base/feeds/3/rename', {
            feedTitle: 'heho'
        }).respond(200, {});

        FeedResource.rename('1sye', 'heho');

        http.flush();

        expect(FeedResource.get('1sye').title).toBe('heho');
    }));


    it ('should move a feed', inject((FeedResource) => {
        http.expectPOST('base/feeds/3/move', {
            parentFolderId: 5
        }).respond(200, {});

        FeedResource.move('1sye', 5);

        http.flush();

        expect(FeedResource.get('1sye').folderId).toBe(5);
    }));


    it ('should create a feed', inject((FeedResource) => {
        http.expectPOST('base/feeds', {
            parentFolderId: 5,
            url: 'hey',
            title: 'ABC'
        }).respond(200, {});

        FeedResource.create('hey', 5, 'abc');

        http.flush();

        expect(FeedResource.get('hey').folderId).toBe(5);
    }));


    it ('should not create a feed if it exists', inject((FeedResource) => {
        http.expectPOST('base/feeds', {
            parentFolderId: 5,
            url: 'ye',
            title: 'ABC'
        }).respond(200, {});

        FeedResource.create('ye', 5, 'abc');

        http.flush();

        expect(FeedResource.size()).toBe(3);
    }));


    it ('should undo a delete folder', inject((FeedResource) => {
        http.expectDELETE('base/feeds/1').respond(200, {});

        FeedResource.delete('ye');

        http.flush();


        http.expectPOST('base/feeds/1/restore').respond(200, {});

        FeedResource.undoDelete();

        http.flush();

        expect(FeedResource.get('ye').id).toBe(1);
    }));




    afterEach(() => {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


});
