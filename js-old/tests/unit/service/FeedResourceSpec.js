/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('FeedResource', function () {
    'use strict';

    var resource,
        http;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
    }));

    afterEach(function () {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


    beforeEach(inject(function (FeedResource, $httpBackend) {
        resource = FeedResource;
        http = $httpBackend;
        FeedResource.receive([
            {id: 1, folderId: 3,  url: 'ye', unreadCount: 45},
            {id: 2, folderId: 4, location: 'test', url: 'sye', unreadCount: 25},
            {id: 3, folderId: 3, title: 'hore', url: '1sye', unreadCount: 0,
             ordering: 0}
        ]);
    }));

    it('should mark all read', inject(function (FeedResource) {

        FeedResource.markRead();

        expect(FeedResource.getUnreadCount()).toBe(0);
    }));

    it('should mark a feed read', inject(function (FeedResource) {

        FeedResource.markFeedRead(1);

        expect(FeedResource.get('ye').unreadCount).toBe(0);
    }));


    it('should mark an item read', inject(function (FeedResource) {

        FeedResource.markItemOfFeedRead(1);

        expect(FeedResource.get('ye').unreadCount).toBe(44);
    }));

    it('should mark an item unread', inject(function (FeedResource) {

        FeedResource.markItemOfFeedUnread(1);

        expect(FeedResource.get('ye').unreadCount).toBe(46);
    }));


    it('should get all of folder', inject(function (FeedResource) {

        var folders = FeedResource.getByFolderId(3);

        expect(folders.length).toBe(2);
    }));



    it('should cache unreadcount', inject(function (FeedResource) {
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


    it('should cache folder unreadcount', inject(function (FeedResource) {
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


    it('should cache unreadcount', inject(function (FeedResource) {
        FeedResource.markItemsOfFeedsRead([1, 2]);
        expect(FeedResource.getUnreadCount()).toBe(68);
    }));



    it ('should reversibly delete a feed', inject(function (FeedResource) {
        http.expectDELETE('base/feeds/2').respond(200, {});

        FeedResource.reversiblyDelete(2);

        http.flush();

        expect(FeedResource.getById(2).deleted).toBe(true);
        expect(FeedResource.getByLocation('test').deleted).toBe(true);
        expect(FeedResource.getUnreadCount()).toBe(70);
    }));


    it ('should rename a feed', inject(function (FeedResource) {
        http.expectPATCH('base/feeds/3', {
            title: 'heho'
        }).respond(200, {});

        FeedResource.patch(3, {title: 'heho'});

        http.flush();
    }));


    it ('should move a feed', inject(function (FeedResource) {
        http.expectPATCH('base/feeds/2', {
            folderId: 5
        }).respond(200, {});

        FeedResource.move(2, 5);

        http.flush();

        expect(FeedResource.get('sye').folderId).toBe(5);
        expect(FeedResource.getFolderUnreadCount(5)).toBe(25);
    }));


    it ('should create a feed and prepend https if not given', inject(function (
    FeedResource) {
        http.expectPOST('base/feeds', {
            parentFolderId: 5,
            url: 'https://hey',
            title: 'abc',
            user: 'john',
            password: 'doe'
        }).respond(200, {});

        FeedResource.create(' hey ', 5, ' abc', 'john', 'doe');

        http.flush();

        expect(FeedResource.get('https://hey').folderId).toBe(5);
    }));


    it ('should create a feed', inject(function (FeedResource) {
        http.expectPOST('base/feeds', {
            parentFolderId: 5,
            url: 'http://hey',
            title: 'abc',
            user: null,
            password: null
        }).respond(200, {});

        FeedResource.create('http://hey', 5, 'abc');

        http.flush();

        expect(FeedResource.get('http://hey').folderId).toBe(5);
    }));


    it ('should display a feed error', inject(function (FeedResource) {
        http.expectPOST('base/feeds', {
            parentFolderId: 5,
            url: 'https://hey',
            title: 'abc',
            user: null,
            password: null
        }).respond(400, {message: 'noo'});

        FeedResource.create('https://hey', 5, 'abc');

        http.flush();

        expect(FeedResource.get('https://hey').error).toBe('noo');
        expect(FeedResource.get('https://hey').faviconLink).toBe('');
    }));


    it ('should create a feed with no folder', inject(function (FeedResource) {
        http.expectPOST('base/feeds', {
            parentFolderId: 0,
            url: 'https://hey',
            user: null,
            password: null
        }).respond(200, {});

        FeedResource.create('hey', undefined);

        expect(FeedResource.get('https://hey').title).toBe('https://hey');
        http.flush();

        expect(FeedResource.get('https://hey').folderId).toBe(0);
    }));


    it ('should undo a delete feed', inject(function (FeedResource) {
        http.expectDELETE('base/feeds/2').respond(200, {});

        FeedResource.reversiblyDelete(2);

        http.flush();


        http.expectPOST('base/feeds/2/restore').respond(200, {});

        FeedResource.undoDelete(2);

        http.flush();

        expect(FeedResource.get('sye').id).toBe(2);
        expect(FeedResource.get('sye').deleted).toBe(false);
        expect(FeedResource.getUnreadCount()).toBe(70);
    }));


    it ('should delete a feed', inject(function (FeedResource) {
        var feed = FeedResource.get('sye');
        var deletedFeed = FeedResource.delete('sye');

        expect(deletedFeed).toBe(feed);
        expect(FeedResource.get('sye')).toBe(undefined);
        expect(FeedResource.size()).toBe(2);
    }));


    it ('should delete feeds of a folder', inject(function (FeedResource) {
        FeedResource.deleteFolder(3);

        expect(FeedResource.get('ye')).toBe(undefined);
        expect(FeedResource.get('1sye')).toBe(undefined);
        expect(FeedResource.getUnreadCount()).toBe(25);
        expect(FeedResource.size()).toBe(1);
    }));


    it ('should reversibly delete a folder', inject(function (FeedResource) {
        http.expectDELETE('base/feeds/1').respond(200, {});
        http.expectDELETE('base/feeds/3').respond(200, {});

        FeedResource.reversiblyDeleteFolder(3);

        http.flush();

        expect(FeedResource.getById(1).deleted).toBe(undefined);
        expect(FeedResource.getById(3).deleted).toBe(undefined);
        expect(FeedResource.getUnreadCount()).toBe(70);
    }));


    it ('should reversibly undelete a folder', inject(function (FeedResource) {
        http.expectDELETE('base/feeds/1').respond(200, {});
        http.expectDELETE('base/feeds/3').respond(200, {});

        FeedResource.reversiblyDeleteFolder(3);

        http.flush();

        http.expectPOST('base/feeds/1/restore').respond(200, {});
        http.expectPOST('base/feeds/3/restore').respond(200, {});

        FeedResource.undoDeleteFolder(3);

        http.flush();

        expect(FeedResource.getById(1).deleted).toBe(false);
        expect(FeedResource.getById(3).deleted).toBe(false);
        expect(FeedResource.getUnreadCount()).toBe(70);
    }));


    it ('should set the feed ordering', inject(function (FeedResource) {
        http.expectPATCH('base/feeds/3', {
            ordering: 2
        }).respond(200, {});

        FeedResource.patch(3, {ordering: 2});

        http.flush();

        expect(FeedResource.getById(3).ordering).toBe(2);
    }));


    it ('should set the feed pinning', inject(function (FeedResource) {
        http.expectPATCH('base/feeds/3', {
            pinned: true
        }).respond(200, {});

        FeedResource.patch(3, {pinned: true});

        http.flush();

        expect(FeedResource.getById(3).pinned).toBe(true);
    }));


    it ('should toggle full text', inject(function (FeedResource) {
        http.expectPATCH('base/feeds/3', {
            fullTextEnabled: true
        }).respond(200, {});

        FeedResource.getById(3).fullTextEnabled = false;
        FeedResource.toggleFullText(3);

        expect(FeedResource.getById(3).fullTextEnabled).toBe(true);
        http.flush();
    }));

});
