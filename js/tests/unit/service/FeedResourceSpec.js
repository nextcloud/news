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

    let resource;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    beforeEach(inject((FeedResource) => {
        resource = FeedResource;
        FeedResource.receive([
            {id: 1, folderId: 3, url: 'ye', unreadCount: 45},
            {id: 2, folderId: 4, url: 'sye', unreadCount: 25},
            {id: 3, folderId: 3, url: '1sye', unreadCount: 0}
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
});
