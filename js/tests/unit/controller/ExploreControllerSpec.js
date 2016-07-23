/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('ExploreController', function () {
    'use strict';

    var controller,
        scope,
        sites;

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
    }));

    beforeEach(inject(function ($controller, $rootScope) {
        scope = $rootScope.$new();
        sites = {
            data: [

            ]
        };

        controller = $controller('ExploreController', {
            $rootScope: scope,
            sites: sites
        });
    }));



    it('should broadcast add feed', inject(function () {
        scope.$broadcast = jasmine.createSpy('broadcast');

        controller.subscribeTo('test');
        expect(scope.$broadcast).toHaveBeenCalledWith('addFeed', 'test');
    }));


    it('should check if a feed is available sites', inject(
    function (FeedResource) {
        FeedResource.add({id: 3, location: 'test', url: 'a'});
        expect(controller.feedExists('test')).toBe(true);
        expect(controller.feedExists('amen')).toBe(false);
    }));


    it('should hide categories without unadded sites', inject(
    function (FeedResource) {
        FeedResource.add({id: 3, location: 'test', url: 'a'});

        var data1 = [{feed: 'test'}, {feed: 'test2'}];
        var data2 = [{feed: 'test'}];

        expect(controller.isCategoryShown(data1)).toBe(true);
        expect(controller.isCategoryShown(data2)).toBe(false);
    }));
});