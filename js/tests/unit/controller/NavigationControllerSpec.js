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
        $provide.constant('ITEM_BATCH_SIZE', 5);
    }));

    beforeEach(inject(($controller) => {
        controller = $controller('NavigationController');
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
});