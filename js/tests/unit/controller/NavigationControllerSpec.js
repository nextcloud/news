/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('NavigationController', function () {
    'use strict';

    var controller;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
        $provide.value('FEED_TYPE', {
            FEED: 0,
            FOLDER: 1,
            STARRED: 2,
            SUBSCRIPTIONS: 3,
            SHARED: 4
        });
        $provide.constant('ITEM_BATCH_SIZE', 5);
    }));

    beforeEach(inject(function ($controller, FeedResource) {
        controller = $controller('NavigationController');
        FeedResource.receive([
            {id: 1, folderId: 3, url: 'ye', unreadCount: 45},
            {id: 2, folderId: 4, url: 'sye', unreadCount: 25},
            {id: 3, folderId: 3, title: 'hore', url: '1sye', unreadCount: 1}
        ]);

    }));


    it('should expose Feeds', inject(function (FeedResource) {
        FeedResource.add({url: 1});
        expect(controller.getFeeds()).toBe(FeedResource.getAll());
    }));


    it('should expose Folders', inject(function (FolderResource) {
        FolderResource.add({name: 1});
        expect(controller.getFolders()).toBe(FolderResource.getAll());
    }));


    it('should mark Folders read', inject(function ($controller) {
        var FeedResource = {
            markFolderRead: jasmine.createSpy('folder'),
            getByFolderId: function () {
                return [
                    {id: 3},
                    {id: 4}
                ];
            }
        };

        var ItemResource = {
            markFeedRead: jasmine.createSpy('feedfolder')
        };

        controller = $controller('NavigationController', {
            FeedResource: FeedResource,
            ItemResource: ItemResource
        });

        controller.markFolderRead(3);

        expect(FeedResource.markFolderRead).toHaveBeenCalledWith(3);
        expect(ItemResource.markFeedRead.callCount).toBe(2);
    }));


    it('should mark a feed read', inject(function ($controller) {
        var FeedResource = {
            markFeedRead: jasmine.createSpy('folder'),
        };

        var ItemResource = {
            markFeedRead: jasmine.createSpy('feedfolder')
        };

        controller = $controller('NavigationController', {
            FeedResource: FeedResource,
            ItemResource: ItemResource
        });

        controller.markFeedRead(3);

        expect(FeedResource.markFeedRead).toHaveBeenCalledWith(3);
        expect(ItemResource.markFeedRead).toHaveBeenCalledWith(3);
    }));


    it('should mark all read', inject(function ($controller) {
        var FeedResource = {
            markRead: jasmine.createSpy('folder'),
        };

        var ItemResource = {
            markRead: jasmine.createSpy('feedfolder')
        };

        controller = $controller('NavigationController', {
            FeedResource: FeedResource,
            ItemResource: ItemResource
        });

        controller.markRead();

        expect(FeedResource.markRead).toHaveBeenCalled();
        expect(ItemResource.markRead).toHaveBeenCalled();
    }));


    it('should mark all read', inject(function (SettingsResource, $controller) {
        var ctrl = $controller('NavigationController', {
            SettingsResource: SettingsResource,
        });

        SettingsResource.set('showAll', true);

        expect(ctrl.isShowAll()).toBe(true);
    }));


    it('should get all of folder', inject(function (FeedResource, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });

        FeedResource.getByFolderId = jasmine.createSpy('getByFolderId');
        ctrl.getFeedsOfFolder(3);

        expect(FeedResource.getByFolderId).toHaveBeenCalledWith(3);
    }));


    it('should get the unreadcount', inject(function (FeedResource,
    $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });


        expect(ctrl.getUnreadCount()).toBe(71);
        expect(ctrl.getFeedUnreadCount(1)).toBe(45);
        expect(ctrl.getFolderUnreadCount(3)).toBe(46);
    }));


    it('should get the starred count', inject(function (ItemResource,
    $controller) {
        var ctrl = $controller('NavigationController', {
            ItemResource: ItemResource,
        });

        ItemResource.receive(99, 'starred');

        expect(ctrl.getStarredCount()).toBe(99);
    }));


    it('should toggle a folder', inject(function (FolderResource, $controller) {
        var ctrl = $controller('NavigationController', {
            FolderResource: FolderResource,
        });

        FolderResource.toggleOpen = jasmine.createSpy('open');

        ctrl.toggleFolder(3);

        expect(FolderResource.toggleOpen).toHaveBeenCalledWith(3);
    }));


    it('should check if a folder has feeds', inject(function (FeedResource,
        $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });

        expect(ctrl.hasFeeds(3)).toBe(true);
        expect(ctrl.hasFeeds(1)).toBe(false);
    }));


    it('should check if a subfeed is active', inject(function (FeedResource,
        FEED_TYPE, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: {
                current: {
                    params: {
                        id: 3
                    },
                    $$route: {
                        type: FEED_TYPE.FEED
                    }
                }
            }
        });

        expect(ctrl.subFeedActive(3)).toBe(true);
    }));


    it('should check if a subscriptions is active', inject(function (
    FeedResource, FEED_TYPE, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: {
                current: {
                    $$route: {
                        type: FEED_TYPE.SUBSCRIPTIONS
                    }
                }
            }
        });

        expect(ctrl.isSubscriptionsActive()).toBe(true);
    }));


    it('should check if a starred is active', inject(function (FeedResource,
        FEED_TYPE, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: {
                current: {
                    $$route: {
                        type: FEED_TYPE.STARRED
                    }
                }
            }
        });

        expect(ctrl.isStarredActive()).toBe(true);
    }));



    it('should check if a feed is active', inject(function (FeedResource,
        FEED_TYPE, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: {
                current: {
                    params: {
                        id: 3
                    },
                    $$route: {
                        type: FEED_TYPE.FEED
                    }
                }
            }
        });

        expect(ctrl.isFeedActive(3)).toBe(true);
    }));


    it('should check if a folder is active', inject(function (FeedResource,
        FEED_TYPE, $controller) {
        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: {
                current: {
                    params: {
                        id: 3
                    },
                    $$route: {
                        type: FEED_TYPE.FOLDER
                    }
                }
            }
        });

        expect(ctrl.isFolderActive(3)).toBe(true);
    }));


    it('should expose check if folder exists', inject(function (
    FolderResource) {
        expect(controller.folderNameExists('hi')).toBe(false);
        FolderResource.add({name: 'hi'});
        expect(controller.folderNameExists('hi')).toBe(true);
    }));


    it('should create a feed with a folderId', inject(function ($controller) {
        var FeedResource = {
            create: jasmine.createSpy('create').andCallFake(
            function (url, folderId) {
                return {
                    then: function (callback) {
                        callback({
                            id: 3,
                            url: url,
                            folderId: folderId
                        });
                    }
                };
            })
        };

        var location = {
            path: jasmine.createSpy('path')
        };

        var Publisher = {
            publishAll: jasmine.createSpy('publishAll')
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            Publisher: Publisher,
            $location: location
        });

        var feed = {
            url: 'test',
            folderId: 3
        };

        ctrl.createFeed(feed);

        expect(ctrl.newFolder).toBe(false);
        expect(FeedResource.create).toHaveBeenCalledWith('test', 3,
            undefined);
        expect(Publisher.publishAll).toHaveBeenCalledWith({
            url: 'test',
            folderId: 3,
            id: 3
        });
        expect(feed.url).toBe('');
        expect(location.path).toHaveBeenCalledWith('/items/feeds/3');
    }));


    it('should create a feed with a foldername', inject(function ($controller) {

        var FeedResource = {
            create: jasmine.createSpy('create').andCallFake(
            function (url, folderId) {
                return {
                    then: function (callback) {
                        callback({
                            url: url,
                            folderId: folderId
                        });
                    }
                };
            })
        };

        var FolderResource = {
            create: jasmine.createSpy('create').andCallFake(
            function (folder) {
                return {
                    then: function (callback) {
                        callback({
                            name: folder
                        });
                    }
                };
            })
        };

        var Publisher = {
            publishAll: jasmine.createSpy('publishAll')
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            Publisher: Publisher,
            FolderResource: FolderResource
        });

        var feed = {
            url: 'test',
            folder: 'john'
        };

        ctrl.createFeed(feed);

        expect(ctrl.newFolder).toBe(false);
        expect(FeedResource.create).toHaveBeenCalledWith('test', 'john',
            undefined);
        expect(FolderResource.create).toHaveBeenCalledWith('john');
        expect(Publisher.publishAll).toHaveBeenCalledWith({
            url: 'test',
            folderId: 'john'
        });
        expect(Publisher.publishAll).toHaveBeenCalledWith({
            name: 'john'
        });
        expect(feed.url).toBe('');
        expect(feed.folder).toBe('');
        expect(feed.folderId).toBe('john');
    }));


    it('should create a folder', inject(function ($controller) {
        var FolderResource = {
            create: jasmine.createSpy('create').andCallFake(
            function (folder) {
                return {
                    then: function (callback) {
                        callback({
                            name: folder
                        });
                    }
                };
            })
        };

        var Publisher = {
            publishAll: jasmine.createSpy('publishAll')
        };

        var ctrl = $controller('NavigationController', {
            Publisher: Publisher,
            FolderResource: FolderResource
        });

        var folder = {
            name: 'test',
        };

        ctrl.createFolder(folder);

        expect(FolderResource.create).toHaveBeenCalledWith('test');
        expect(Publisher.publishAll).toHaveBeenCalledWith({
            name: 'test'
        });
        expect(folder.name).toBe('');
    }));


    it('should move a feed', inject(function ($controller, FEED_TYPE,
    FeedResource) {
        FeedResource.move = jasmine.createSpy('move');

        var route = {
            reload: jasmine.createSpy('reload'),
            current: {
                $$route: {
                    type: FEED_TYPE.FOLDER
                },
                params: {
                    id: 2
                }
            }
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: route
        });

        ctrl.moveFeed(1, 4);

        expect(FeedResource.move).toHaveBeenCalledWith(1, 4);
        expect(route.reload).not.toHaveBeenCalled();
    }));


    it('should not move a feed if nothing changed', inject(function (
    $controller, FEED_TYPE, FeedResource) {
        FeedResource.move = jasmine.createSpy('move');

        var route = {
            reload: jasmine.createSpy('reload'),
            current: {
                $$route: {
                    type: FEED_TYPE.FOLDER
                },
                params: {
                    id: 2
                }
            }
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: route
        });

        ctrl.moveFeed(1, 3);

        expect(FeedResource.move).not.toHaveBeenCalled();
        expect(route.reload).not.toHaveBeenCalled();
    }));



    it('should reload if a feed is moved from active folder', inject(
    function ($controller, FEED_TYPE, FeedResource) {
        FeedResource.move = jasmine.createSpy('move');

        var route = {
            reload: jasmine.createSpy('reload'),
            current: {
                $$route: {
                    type: FEED_TYPE.FOLDER
                },
                params: {
                    id: 3
                }
            }
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: route
        });

        ctrl.moveFeed(3, 5);

        expect(route.reload).toHaveBeenCalled();
    }));


    it('should reload if a feed is moved into active folder', inject(
    function ($controller, FEED_TYPE, FeedResource) {
        FeedResource.move = jasmine.createSpy('move');

        var route = {
            reload: jasmine.createSpy('reload'),
            current: {
                $$route: {
                    type: FEED_TYPE.FOLDER
                },
                params: {
                    id: 5
                }
            }
        };

        var ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
            $route: route
        });

        ctrl.moveFeed(3, 5);

        expect(route.reload).toHaveBeenCalled();
    }));


});
