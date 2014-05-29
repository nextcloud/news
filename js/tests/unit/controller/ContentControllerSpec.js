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
        $provide.constant('BASE_URL', 'base');
        $provide.constant('ITEM_BATCH_SIZE', 5);
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
                },
                {
                    id: 5,
                    feedId: 4,
                    keepUnread: true
                }]
            },
        });

        ctrl.markRead(3);
        ctrl.markRead(5);

        expect(ItemResource.markItemRead).toHaveBeenCalledWith(3);
        expect(FeedResource.markItemOfFeedRead).toHaveBeenCalledWith(4);
        expect(ItemResource.markItemRead.callCount).toBe(1);
        expect(FeedResource.markItemOfFeedRead.callCount).toBe(1);
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


    it('should mark multiple items read', inject(($controller,
        ItemResource, FeedResource, Publisher) => {

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.markItemsRead = jasmine.createSpy('markRead');
        FeedResource.markItemOfFeedRead = jasmine.createSpy('markRead');

        let ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 4
                },
                {
                    id: 2,
                    feedId: 4,
                    keepUnread: true
                },
                {
                    id: 1,
                    feedId: 4
                },]
            },
        });

        ctrl.scrollRead([3, 2, 1]);

        expect(ItemResource.markItemsRead).toHaveBeenCalledWith([3, 1]);
        expect(FeedResource.markItemOfFeedRead.callCount).toBe(2);
    }));


    it('should not autopage if less than 0 elements', inject((
        $controller, ItemResource, Publisher) => {

        let $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        let $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.autoPage = jasmine.createSpy('autoPage')
            .andCallFake(() => {
                return {
                    success: (callback) => {
                        callback({
                            'items': []
                        });

                        return {
                            error: () => {}
                        };
                    }
                }
        });

        let ctrl = $controller('ContentController', {
            $routeParams: $routeParams,
            $route: $route,
            Publisher: Publisher,
            ItemResource: ItemResource,
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);

        ctrl.autoPage();

        expect(ctrl.autoPagingEnabled()).toBe(false);

        expect(ItemResource.autoPage).toHaveBeenCalledWith(3, 2);

    }));


    it('should autopage if more than 0 elements', inject((
        $controller, ItemResource, Publisher) => {

        let $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        let $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.autoPage = jasmine.createSpy('autoPage')
            .andCallFake(() => {
                return {
                    success: (callback) => {
                        callback({
                            'items': [{items: [{id: 3}]}]
                        });

                        return {
                            error: () => {}
                        };
                    }
                }
        });

        let ctrl = $controller('ContentController', {
            $routeParams: $routeParams,
            $route: $route,
            Publisher: Publisher,
            ItemResource: ItemResource,
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);

        ctrl.autoPage();

        expect(ctrl.autoPagingEnabled()).toBe(true);
        expect(ItemResource.size()).toBe(1);
    }));


    it('should autopage if error', inject((
        $controller, ItemResource, Publisher) => {

        let $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        let $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels('items');
        ItemResource.autoPage = jasmine.createSpy('autoPage')
            .andCallFake(() => {
                return {
                    success: (callback) => {
                        callback({
                            'items': []
                        });

                        return {
                            error: (callback) => {
                                callback();
                            }
                        };
                    }
                }
        });

        let ctrl = $controller('ContentController', {
            $routeParams: $routeParams,
            $route: $route,
            Publisher: Publisher,
            ItemResource: ItemResource,
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);

        ctrl.autoPage();

        expect(ctrl.autoPagingEnabled()).toBe(true);
    }));

});
