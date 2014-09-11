/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('SettingsController', function () {
    'use strict';

    var route;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
    }));

    beforeEach(function () {
        route = {
            reload: jasmine.createSpy('Route')
        };
    });

    it('should set values', inject(function ($controller) {
        var SettingsResource = {
            set: jasmine.createSpy('SettingsResource'),
            get: function (key) { return key; }
        };

        var ctrl = $controller('SettingsController', {
            SettingsResource: SettingsResource,
            $route: route
        });

        ctrl.toggleSetting(3);

        expect(SettingsResource.set).toHaveBeenCalledWith(3, false);
    }));


    it('should reload page if set needed', inject(function ($controller) {
        var SettingsResource = {
            set: jasmine.createSpy('SettingsResource'),
            get: function (key) { return key; }
        };

        var ctrl = $controller('SettingsController', {
            SettingsResource: SettingsResource,
            $route: route
        });

        ctrl.toggleSetting('showAll');
        ctrl.toggleSetting('oldestFirst');

        expect(SettingsResource.set).toHaveBeenCalledWith('showAll', false);
        expect(route.reload).toHaveBeenCalled();
        expect(route.reload.callCount).toBe(2);
    }));


    it('should return feed size', inject(function ($controller, FeedResource) {
        FeedResource.add({url: 'hi'});

        var ctrl = $controller('SettingsController', {
            FeedResource: FeedResource,
            $route: route
        });

        expect(ctrl.feedSize()).toBe(1);
    }));
});