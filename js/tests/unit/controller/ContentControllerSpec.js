/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ContentController', () => {
    'use strict';


    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    it('should publish data to models', inject(($controller, Publisher,
        FeedResource, ItemResource) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        Publisher.subscribe(FeedResource).toChannels('feeds');

        let controller = $controller('ContentController', {
            data: {
                'items': [
                    {id: 3},
                    {id: 4}
                ]
            },
        });

        expect(controller.getItems().length).toBe(2);
    }));


    it('should clear data on url change', inject(($controller,
        ItemResource) => {

        ItemResource.clear = jasmine.createSpy('clear');

        $controller('ContentController', {
            data: {},
        });

        expect(ItemResource.clear).toHaveBeenCalled();
    }));


    it('should return order by', inject(($controller,
        SettingsResource) => {

        let ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.orderBy()).toBe('id');

        SettingsResource.set('oldestFirst', true);

        expect(ctrl.orderBy()).toBe('-id');
    }));


    it('should mark read', inject(($controller,
        ItemResource, FeedResource, Publisher) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.markItemRead = jasmine.createSpy('markRead');
        FeedResource.markItemOfFeedRead = jasmine.createSpy('markRead');

        let ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 4
                }]
            },
        });

        ctrl.markRead(3);

        expect(ItemResource.markItemRead).toHaveBeenCalledWith(3);
        expect(FeedResource.markItemOfFeedRead).toHaveBeenCalledWith(4);
    }));


    it('should toggle keep unread when unread', inject(($controller,
        ItemResource, FeedResource, Publisher) => {

        Publisher.subscribe(ItemResource).toChannels('items');

        let ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 4,
                    unread: true
                }]
            },
        });

        ctrl.toggleKeepUnread(3);

        expect(ItemResource.get(3).keepUnread).toBe(true);
    }));


    it('should toggle keep unread when read', inject(($controller,
        ItemResource, FeedResource, Publisher) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.markItemRead = jasmine.createSpy('markRead');
        FeedResource.markItemOfFeedUnread = jasmine.createSpy('markRead');

        let ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 4,
                    unread: false,
                    keepUnread: true
                }]
            },
        });

        ctrl.toggleKeepUnread(3);

        expect(ItemResource.get(3).keepUnread).toBe(false);
        expect(ItemResource.markItemRead).toHaveBeenCalledWith(3, false);
        expect(FeedResource.markItemOfFeedUnread).toHaveBeenCalledWith(4);
    }));


    it('should get a feed', inject(($controller, FeedResource, Publisher) => {

        Publisher.subscribe(FeedResource).toChannels('feeds');

        let ctrl = $controller('ContentController', {
            FeedResource: FeedResource,
            data: {
                'feeds': [{
                    id: 3,
                    url: 4
                }]
            },
        });

        expect(ctrl.getFeed(3).url).toBe(4);
    }));


    it('should toggle starred', inject(($controller, ItemResource) => {

        ItemResource.toggleStar = jasmine.createSpy('star');

        let ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            data: {},
        });

        ctrl.toggleStar(3);

        expect(ItemResource.toggleStar).toHaveBeenCalledWith(3);
    }));



    it('should publish compactview', inject(($controller, SettingsResource) => {

        SettingsResource.set('compact', true);

        let ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.isCompactView()).toBe(true);
    }));


    it('should publish markread', inject(($controller, SettingsResource) => {

        SettingsResource.set('preventReadOnScroll', true);

        let ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.markReadEnabled()).toBe(false);
    }));


    it('should publish autopaging', inject(($controller) => {
        let ctrl = $controller('ContentController', {
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);
    }));


});
