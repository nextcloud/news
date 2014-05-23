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

    let resource;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    beforeEach(inject((FeedResource) => {
        resource = FeedResource;
        FeedResource.add({id: 1, url: 'ye', unreadCount: 45});
        FeedResource.add({id: 2, url: 'sye', unreadCount: 25});
        FeedResource.add({id: 3, url: '1sye', unreadCount: 0});
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
});
