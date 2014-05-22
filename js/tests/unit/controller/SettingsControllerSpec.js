/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('SettingsController', () => {
    'use strict';

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));


    it('should set values', inject(($controller) => {
        let Settings = {
            set: jasmine.createSpy('Settings'),
            get: key => key
        };

        let ctrl = $controller('SettingsController', {
            Settings: Settings
        });

        ctrl.toggleSetting(3);

        expect(Settings.set).toHaveBeenCalledWith(3, false);
    }));


    it('should reload page if set needed', inject(($controller) => {
        let settings = {
            set: jasmine.createSpy('Settings'),
            get: key => key
        };

        let route = {
            reload: jasmine.createSpy('Route')
        };

        let ctrl = $controller('SettingsController', {
            Settings: settings,
            $route: route
        });

        ctrl.toggleSetting('showAll');
        ctrl.toggleSetting('oldestFirst');

        expect(settings.set).toHaveBeenCalledWith('showAll', false);
        expect(route.reload).toHaveBeenCalled();
        expect(route.reload.callCount).toBe(2);
    }));


    it('should return feed size', inject(($controller, FeedResource) => {
        FeedResource.add({url: 'hi'});

        let ctrl = $controller('SettingsController', {
            FeedResource: FeedResource
        });

        expect(ctrl.feedSize()).toBe(1);
    }));
});