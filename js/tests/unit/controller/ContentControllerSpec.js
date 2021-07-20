/**
 * Nextcloud - News
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
        $provide.constant('ITEM_AUTO_PAGE_SIZE', 1);
        $provide.constant('FEED_TYPE', {
            FEED: 0,
            FOLDER: 1,
            STARRED: 2,
            SUBSCRIPTIONS: 3,
            SHARED: 4
        });
        $provide.constant('$route', {
            current: {
                $$route: {
                    type: 3
                }
            }
        });
    }));


    it('should publish data to models',
        inject(function ($controller, Publisher, FeedResource, ItemResource) {

            Publisher.subscribe(ItemResource).toChannels(['items']);
            Publisher.subscribe(FeedResource).toChannels(['feeds']);

            var controller = $controller('ContentController', {
                data: {
                    'items': [
                        {id: 3, fingerprint: 'a'},
                        {id: 4, fingerprint: 'b'}
                    ]
                }
            });

            expect(controller.getItems().length).toBe(2);
        }));


    it('should clear data on url change', inject(function ($controller,
                                                           ItemResource) {

        ItemResource.clear = jasmine.createSpy('clear');

        $controller('ContentController', {
            data: {}
        });

        expect(ItemResource.clear).toHaveBeenCalled();
    }));


    it('should sort feed items', inject(function ($controller) {
        var ctrl = $controller('ContentController', {
            data: {}
        });
        var first = {value: 11, type: 'number'};
        var second = {value: 12, type: 'number'};
        var third = {value: 101, type: 'number'};
        expect(ctrl.sortIds(first, second)).toBe(1);
        expect(ctrl.sortIds(second, first)).toBe(-1);
        expect(ctrl.sortIds(second, second)).toBe(-1);
        expect(ctrl.sortIds(first, third)).toBe(1);
    }));


    it('should return order if custom ordering',
        inject(function ($controller, SettingsResource, FeedResource,
                         FEED_TYPE) {
            var route = {
                current: {
                    $$route: {
                        type: FEED_TYPE.FEED
                    }
                }
            };
            FeedResource.receive([
                {id: 1, folderId: 3, url: 'ye', unreadCount: 45, ordering: 1},
            ]);
            var ctrl = $controller('ContentController', {
                data: {},
                $route: route,
                $routeParams: {
                    id: 1
                }
            });

            expect(ctrl.oldestFirst).toBe(true);

            SettingsResource.set('oldestFirst', false);

            expect(ctrl.oldestFirst).toBe(true);
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
                'items': [
                    {
                        id: 3,
                        feedId: 4,
                        fingerprint: 'a',
                        unread: true
                    },
                    {
                        id: 5,
                        feedId: 4,
                        fingerprint: 'b',
                        keepUnread: true
                    },
                    {
                        id: 9,
                        feedId: 5,
                        fingerprint: 'c',
                        unread: false
                    }]
            },
        });

        ctrl.markRead(3);
        ctrl.markRead(5);
        ctrl.markRead(9);

        expect(ItemResource.markItemRead).toHaveBeenCalledWith(3);
        expect(FeedResource.markItemOfFeedRead).toHaveBeenCalledWith(4);
        expect(ItemResource.markItemRead.calls.count()).toBe(1);
        expect(FeedResource.markItemOfFeedRead.calls.count()).toBe(1);
    }));


    it('should toggle keep unread when unread',
        inject(function ($controller, ItemResource, FeedResource, Publisher) {

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


    it('should toggle keep unread when read',
        inject(function ($controller, ItemResource, FeedResource, Publisher) {

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


    it('should publish compact expand setting',
        inject(function ($controller, SettingsResource) {

            SettingsResource.set('compactExpand', true);

            var ctrl = $controller('ContentController', {
                SettingsResource: SettingsResource,
                data: {},
            });

            expect(ctrl.isCompactExpand()).toBe(true);
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


    it('should mark multiple items read',
        inject(function ($controller, ItemResource, FeedResource, Publisher) {

            Publisher.subscribe(ItemResource).toChannels(['items']);
            ItemResource.markItemsRead = jasmine.createSpy('markRead');
            FeedResource.markItemsOfFeedsRead = jasmine.createSpy('markRead');

            var ctrl = $controller('ContentController', {
                ItemResource: ItemResource,
                FeedResource: FeedResource,
                data: {
                    'items': [
                        {
                            id: 3,
                            fingerprint: 'a',
                            feedId: 6
                        },
                        {
                            id: 2,
                            fingerprint: 'b',
                            feedId: 4,
                            keepUnread: true
                        },
                        {
                            id: 1,
                            fingerprint: 'c',
                            feedId: 4
                        },]
                },
            });

            ctrl.scrollRead([3, 2, 1]);

            expect(ItemResource.markItemsRead).toHaveBeenCalledWith([3, 1]);
            expect(FeedResource.markItemsOfFeedsRead)
                .toHaveBeenCalledWith([6, 4]);
        }));


    it('should not autopage if less than 0 elements',
        inject(function ($controller, ItemResource, Publisher,
                         SettingsResource, Loading) {
            SettingsResource.set('oldestFirst', true);
            SettingsResource.set('showAll', false);

            var $location = {
                search: jasmine.createSpy('search').and.returnValue({
                    search: 'some+string'
                })
            };

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
                .and.callFake(function () {
                    return {
                        then: function (callback) {
                            callback({
                                data: {'items': []}
                            });

                            return {
                                finally: function (callback) {
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
                SettingsResource: SettingsResource,
                data: {'items': [{id: 3}, {id: 4}]},
                $location: $location
            });

            expect(ctrl.autoPagingEnabled()).toBe(true);

            ctrl.autoPage();

            expect(ctrl.autoPagingEnabled()).toBe(false);

            expect(Loading.isLoading('autopaging')).toBe(false);
            expect(ItemResource.autoPage)
                .toHaveBeenCalledWith(3, 2, true, false, 'some+string');

        }));


    it ('should toggle active item', inject(function ($controller) {
        var ctrl = $controller('ContentController', {
            data: {'items': [{id: 3}, {id: 4}]}
        });
        expect(ctrl.isItemActive(3)).toBe(false);
        ctrl.setItemActive(3);
        expect(ctrl.isItemActive(4)).toBe(false);
        expect(ctrl.isItemActive(3)).toBe(true);
    }));

    it('should autopage if more than 0 elements',
        inject(function ($controller, ItemResource, Publisher) {

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
                .and.callFake(function () {
                    return {
                        then: function (callback) {
                            callback({
                                data: {
                                    'items': [
                                        {items: [{id: 3, fingerprint: 'a'}]}]}
                            });

                            return {
                                finally: function () {
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
                data: {
                    'items': [{
                        id: 3, fingerprint: 'a'
                    }, {
                        id: 4, fingerprint: 'b'
                    }]
                },
            });

            expect(ctrl.autoPagingEnabled()).toBe(true);

            ctrl.autoPage();

            expect(ctrl.autoPagingEnabled()).toBe(true);
            expect(ItemResource.size()).toBe(3);
        }));


    it('should autopage if error',
        inject(function ($controller, ItemResource, Publisher) {

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
                .and.callFake(function () {
                    return {
                        then: function (callback, errorCallback) {
                            callback({
                                data: {'items': []}
                            }, errorCallback({}));

                            return {
                                finally: function () {
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
                data: {
                    'items': [
                        {
                            id: 3,
                            fingerprint: 'a'
                        }, {
                            id: 4,
                            fingerprint: 'b'
                        }]
                },
            });

            expect(ctrl.autoPagingEnabled()).toBe(true);

            ctrl.autoPage();

            expect(ctrl.autoPagingEnabled()).toBe(true);
        }));

    it('should refresh the page', inject(function ($controller) {
        var route = {
            current: {
                $$route: {
                    type: 3
                }
            },
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

    it('should publish showall', inject(function ($controller,
                                                  SettingsResource) {

        SettingsResource.set('showAll', true);

        var ctrl = $controller('ContentController', {
            SettingsResource: SettingsResource,
            data: {},
        });

        expect(ctrl.isShowAll()).toBe(true);
    }));


    it('should return the correct media type', inject(function ($controller) {

        var ctrl = $controller('ContentController', {
            data: {},
        });

        expect(ctrl.getMediaType('audio/test')).toBe('audio');
        expect(ctrl.getMediaType('video/test')).toBe('video');
        expect(ctrl.getMediaType('vides/test')).toBe(undefined);
    }));

});
