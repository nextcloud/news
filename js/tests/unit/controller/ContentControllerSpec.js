/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ContentController', function () {
    'use strict';


    beforeEach(module('News', function ($provide) {
        $provide.constant('BASE_URL', 'base');
        $provide.constant('ITEM_BATCH_SIZE', 5);
        $provide.constant('FEED_TYPE', {
            FEED: 0,
            FOLDER: 1,
            STARRED: 2,
            SUBSCRIPTIONS: 3,
            SHARED: 4
        });
    }));


    it('should publish data to models', inject(function ($controller, Publisher,
        FeedResource, ItemResource) {

        Publisher.subscribe(ItemResource).toChannels(['items']);
        Publisher.subscribe(FeedResource).toChannels(['feeds']);

        var controller = $controller('ContentController', {
            data: {
                'items': [
                    {id: 3},
                    {id: 4}
                ]
            }
        });

        expect(controller.getItems().length).toBe(2);
    }));


    it('should clear data on url change', inject(function ($controller,
        ItemResource) {

        ItemResource.clear = jasmine.createSpy('clear');

        $controller('ContentController', {
            data: {},
        });

        expect(ItemResource.clear).toHaveBeenCalled();
    }));


    it('should return order by', inject(function ($controller,
        SettingsResource) {

        var ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.orderBy()).toBe('-id');

        SettingsResource.set('oldestFirst', true);

        expect(ctrl.orderBy()).toBe('id');
    }));


    it('should mark read', inject(function ($controller, ItemResource,
        FeedResource, Publisher) {

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.markItemRead = jasmine.createSpy('markRead');
        FeedResource.markItemOfFeedRead = jasmine.createSpy('markRead');

        var ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 4,
                    unread: true
                },
                {
                    id: 5,
                    feedId: 4,
                    keepUnread: true
                },
                {
                    id: 9,
                    feedId: 5,
                    unread: false
                }]
            },
        });

        ctrl.markRead(3);
        ctrl.markRead(5);
        ctrl.markRead(9);

        expect(ItemResource.markItemRead).toHaveBeenCalledWith(3);
        expect(FeedResource.markItemOfFeedRead).toHaveBeenCalledWith(4);
        expect(ItemResource.markItemRead.callCount).toBe(1);
        expect(FeedResource.markItemOfFeedRead.callCount).toBe(1);
    }));


    it('should toggle keep unread when unread', inject(function ($controller,
        ItemResource, FeedResource, Publisher) {

        Publisher.subscribe(ItemResource).toChannels(['items']);

        var ctrl = $controller('ContentController', {
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


    it('should toggle keep unread when read', inject(function ($controller,
        ItemResource, FeedResource, Publisher) {

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.markItemRead = jasmine.createSpy('markRead');
        FeedResource.markItemOfFeedUnread = jasmine.createSpy('markRead');

        var ctrl = $controller('ContentController', {
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


    it('should get a feed', inject(function ($controller, FeedResource,
    Publisher) {

        Publisher.subscribe(FeedResource).toChannels(['feeds']);

        var ctrl = $controller('ContentController', {
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


    it('should toggle starred', inject(function ($controller, ItemResource) {

        ItemResource.toggleStar = jasmine.createSpy('star');

        var ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            data: {},
        });

        ctrl.toggleStar(3);

        expect(ItemResource.toggleStar).toHaveBeenCalledWith(3);
    }));



    it('should publish compactview', inject(function ($controller,
    SettingsResource) {

        SettingsResource.set('compact', true);

        var ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.isCompactView()).toBe(true);
    }));


    it('should publish markread', inject(function ($controller,
    SettingsResource) {

        SettingsResource.set('preventReadOnScroll', true);

        var ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.markReadEnabled()).toBe(false);
    }));


    it('should publish autopaging', inject(function ($controller) {
        var ctrl = $controller('ContentController', {
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);
    }));


    it('should mark multiple items read', inject(function ($controller,
        ItemResource, FeedResource, Publisher) {

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.markItemsRead = jasmine.createSpy('markRead');
        FeedResource.markItemsOfFeedsRead = jasmine.createSpy('markRead');

        var ctrl = $controller('ContentController', {
            ItemResource: ItemResource,
            FeedResource: FeedResource,
            data: {
                'items': [{
                    id: 3,
                    feedId: 6
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
        expect(FeedResource.markItemsOfFeedsRead).toHaveBeenCalledWith([6, 4]);
    }));


    it('should not autopage if less than 0 elements', inject(function (
        $controller, ItemResource, Publisher, SettingsResource) {
        SettingsResource.set('oldestFirst', true);

        var $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        var $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.autoPage = jasmine.createSpy('autoPage')
        .andCallFake(function () {
            return {
                success: function (callback) {
                    callback({
                        'items': []
                    });

                    return {
                        error: function () {}
                    };
                }
            };
        });

        var ctrl = $controller('ContentController', {
            $routeParams: $routeParams,
            $route: $route,
            Publisher: Publisher,
            ItemResource: ItemResource,
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.autoPagingEnabled()).toBe(true);

        ctrl.autoPage();

        expect(ctrl.autoPagingEnabled()).toBe(false);

        expect(ItemResource.autoPage).toHaveBeenCalledWith(3, 2, true);

    }));


    it('should autopage if more than 0 elements', inject(function (
        $controller, ItemResource, Publisher) {

        var $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        var $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.autoPage = jasmine.createSpy('autoPage')
        .andCallFake(function () {
            return {
                success: function (callback) {
                    callback({
                        'items': [{items: [{id: 3}]}]
                    });

                    return {
                        error: function () {}
                    };
                }
            };
        });

        var ctrl = $controller('ContentController', {
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


    it('should autopage if error', inject(function (
        $controller, ItemResource, Publisher) {

        var $route = {
            current: {
                $$route: {
                    type: 3
                }
            }
        };

        var $routeParams = {
            id: 2
        };

        Publisher.subscribe(ItemResource).toChannels(['items']);
        ItemResource.autoPage = jasmine.createSpy('autoPage')
        .andCallFake(function () {
            return {
                success: function (callback) {
                    callback({
                        'items': []
                    });

                    return {
                        error: function (callback) {
                            callback();
                        }
                    };
                }
            };
        });

        var ctrl = $controller('ContentController', {
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


    it('should return relative date', inject(function ($controller,
        SettingsResource) {

        SettingsResource.receive({language: 'en'});
        var ctrl = $controller('ContentController', {
            data: {},
        });

        expect(ctrl.getRelativeDate(12)).not.toBe('');
    }));


    it('should return relative date empty', inject(function ($controller) {
        var ctrl = $controller('ContentController', {
            data: {},
        });

        expect(ctrl.getRelativeDate('')).toBe('');
    }));

    it('should refresh the page', inject(function ($controller) {
        var route = {
            reload: jasmine.createSpy('reload')
        };
        var ctrl = $controller('ContentController', {
            data: {},
            $route: route
        });

        ctrl.refresh();

        expect(route.reload).toHaveBeenCalled();
    }));

    it('should tell if a feed is shown', inject(function ($controller,
        FEED_TYPE) {

        var $route = {
            current: {
                $$route: {
                    type: 0
                }
            }
        };

        var ctrl = $controller('ContentController', {
            $route: $route,
            FEED_TYPE: FEED_TYPE,
            data: {}
        });


        Object.keys(FEED_TYPE).forEach(function (key) {
            $route.current.$$route.type = FEED_TYPE[key];
            if (key === 'FEED') {
                expect(ctrl.isFeed()).toBe(true);
            } else {
                expect(ctrl.isFeed()).toBe(false);
            }
        });

    }));


    it('should redirect to the explore page if there are no feeds and folders',
    inject(function ($controller) {
        var location = {
            path: jasmine.createSpy('reload')
        };
        $controller('ContentController', {
            data: {},
            $location: location
        });

        expect(location.path).toHaveBeenCalledWith('/explore');
    }));

    it('should not redirect to the explore page if there are feeds and folders',
    inject(function ($controller, FolderResource, FeedResource) {

        FolderResource.add({id: 3, name: 'test'});

        var location = {
            path: jasmine.createSpy('reload')
        };
        $controller('ContentController', {
            data: {},
            $location: location
        });

        expect(location.path).not.toHaveBeenCalledWith('/explore');

        FolderResource.clear({id: 3, name: 'test'});
        FeedResource.add({id: 3, url: 'test'});

        location = {
            path: jasmine.createSpy('reload')
        };
        $controller('ContentController', {
            data: {},
            $location: location
        });

        expect(location.path).not.toHaveBeenCalledWith('/explore');
    }));

});
