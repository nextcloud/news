/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('NavigationController', () => {
    'use strict';

    let controller;

    beforeEach(module('News', ($provide) => {
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

    beforeEach(inject(($controller, FeedResource) => {
        controller = $controller('NavigationController');
        FeedResource.receive([
            {id: 1, folderId: 3, url: 'ye', unreadCount: 45},
            {id: 2, folderId: 4, url: 'sye', unreadCount: 25},
            {id: 3, folderId: 3, title: 'hore', url: '1sye', unreadCount: 1}
        ]);

    }));


    it('should expose Feeds', inject((FeedResource) => {
        FeedResource.add({url: 1});
        expect(controller.getFeeds()).toBe(FeedResource.getAll());
    }));


    it('should expose Folders', inject((FolderResource) => {
        FolderResource.add({name: 1});
        expect(controller.getFolders()).toBe(FolderResource.getAll());
    }));


    it('should mark Folders read', inject(($controller) => {
        let FeedResource = {
            markFolderRead: jasmine.createSpy('folder'),
            getByFolderId: () => {
                return [
                    {id: 3},
                    {id: 4}
                ];
            }
        };

        let ItemResource = {
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


    it('should mark a feed read', inject(($controller) => {
        let FeedResource = {
            markFeedRead: jasmine.createSpy('folder'),
        };

        let ItemResource = {
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


    it('should mark all read', inject(($controller) => {
        let FeedResource = {
            markRead: jasmine.createSpy('folder'),
        };

        let ItemResource = {
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


    it('should mark all read', inject((SettingsResource, $controller) => {
        let ctrl = $controller('NavigationController', {
            SettingsResource: SettingsResource,
        });

        SettingsResource.set('showAll', true);

        expect(ctrl.isShowAll()).toBe(true);
    }));


    it('should get all of folder', inject((FeedResource, $controller) => {
        let ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });

        FeedResource.getByFolderId = jasmine.createSpy('getByFolderId');
        ctrl.getFeedsOfFolder(3);

        expect(FeedResource.getByFolderId).toHaveBeenCalledWith(3);
    }));


    it('should get the unreadcount', inject((FeedResource, $controller) => {
        let ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });


        expect(ctrl.getUnreadCount()).toBe(71);
        expect(ctrl.getFeedUnreadCount(1)).toBe(45);
        expect(ctrl.getFolderUnreadCount(3)).toBe(46);
    }));


    it('should get the starred count', inject((ItemResource, $controller) => {
        let ctrl = $controller('NavigationController', {
            ItemResource: ItemResource,
        });

        ItemResource.receive(99, 'starred');

        expect(ctrl.getStarredCount()).toBe(99);
    }));


    it('should toggle a folder', inject((FolderResource, $controller) => {
        let ctrl = $controller('NavigationController', {
            FolderResource: FolderResource,
        });

        FolderResource.toggleOpen = jasmine.createSpy('open');

        ctrl.toggleFolder(3);

        expect(FolderResource.toggleOpen).toHaveBeenCalledWith(3);
    }));


    it('should check if a folder has feeds', inject((FeedResource,
        $controller) => {
        let ctrl = $controller('NavigationController', {
            FeedResource: FeedResource,
        });

        expect(ctrl.hasFeeds(3)).toBe(true);
        expect(ctrl.hasFeeds(1)).toBe(false);
    }));


    it('should check if a subfeed is active', inject((FeedResource,
        FEED_TYPE, $controller) => {
        let ctrl = $controller('NavigationController', {
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

    it('should check if a subscriptions is active', inject((FeedResource,
        FEED_TYPE, $controller) => {
        let ctrl = $controller('NavigationController', {
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


    it('should check if a starred is active', inject((FeedResource,
        FEED_TYPE, $controller) => {
        let ctrl = $controller('NavigationController', {
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



    it('should check if a feed is active', inject((FeedResource,
        FEED_TYPE, $controller) => {
        let ctrl = $controller('NavigationController', {
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


    it('should check if a folder is active', inject((FeedResource,
        FEED_TYPE, $controller) => {
        let ctrl = $controller('NavigationController', {
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


    it('should expose check if folder exists', inject((FolderResource) => {
        expect(controller.folderNameExists('hi')).toBe(false);
        FolderResource.add({name: 'hi'});
        expect(controller.folderNameExists('hi')).toBe(true);
    }));

});
