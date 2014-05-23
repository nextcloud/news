/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('SettingsResource', () => {
    'use strict';

    let http;

    beforeEach(module('News', ($provide) => {
        $provide.value('BASE_URL', 'base');
    }));

    beforeEach(inject(($httpBackend) => {
        http = $httpBackend;
    }));


    it('should receive default SettingsResource', inject((SettingsResource) => {
        SettingsResource.receive({
            'showAll': true
        });

        expect(SettingsResource.get('showAll')).toBe(true);
    }));


    it('should set values', inject((SettingsResource) => {
        http.expectPOST('base/settings', {showAll: true}).respond(200, {});

        SettingsResource.set('showAll', true);

        http.flush();

        expect(SettingsResource.get('showAll')).toBe(true);
    }));


    afterEach(() => {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


});