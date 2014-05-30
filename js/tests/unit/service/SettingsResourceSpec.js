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


    it('should set language codes', inject((SettingsResource) => {
        let codes = [
            'ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en-ca',
            'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he',
            'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne',
            'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr',
            'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw', 'hi'
        ];

        for (let code of codes) {
            SettingsResource.receive({
                language: code
            });
            expect(SettingsResource.get('language')).toBe(code);
        }
    }));


    it('should set default language codes', inject((SettingsResource) => {
        SettingsResource.receive({
            language: 'abc'
        });
        expect(SettingsResource.get('language')).toBe('en');
    }));


    it('should fix broken language codes', inject((SettingsResource) => {
        SettingsResource.receive({
            language: 'EN_CA'
        });
        expect(SettingsResource.get('language')).toBe('en-ca');
    }));


    it('should fall back to more general language code', inject((
        SettingsResource) => {

        SettingsResource.receive({
            language: 'EN_US'
        });
        expect(SettingsResource.get('language')).toBe('en');
    }));


});