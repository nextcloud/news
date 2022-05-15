/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('SettingsResource', function () {
    'use strict';

    var http;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
    }));

    beforeEach(inject(function ($httpBackend) {
        http = $httpBackend;
    }));

    afterEach(function () {
        http.verifyNoOutstandingExpectation();
        http.verifyNoOutstandingRequest();
    });


    it('should receive default SettingsResource', inject(
    function (SettingsResource) {
        SettingsResource.receive({
            'showAll': true
        });

        expect(SettingsResource.get('showAll')).toBe(true);
    }));


    it('should set values', inject(function (SettingsResource) {
        http.expectPUT('base/settings',  {
            'language': 'en',
            'showAll': true,
            'compact': false,
            'oldestFirst': null,
            'compactExpand': false,
            'preventReadOnScroll': false
        }).respond(200, {});

        SettingsResource.set('showAll', true);

        http.flush();

        expect(SettingsResource.get('showAll')).toBe(true);
    }));


    it('should set language codes', inject(function (SettingsResource) {
        var codes = [
            'ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en-ca',
            'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he',
            'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne',
            'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr',
            'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw', 'hi'
        ];

        codes.forEach(function (code) {
            SettingsResource.receive({
                language: code
            });
            expect(SettingsResource.get('language')).toBe(code);
        });
    }));


    it('should set default language codes', inject(function (SettingsResource) {
        SettingsResource.receive({
            language: 'abc'
        });
        expect(SettingsResource.get('language')).toBe('en');
    }));


    it('should fix broken language codes', inject(function (SettingsResource) {
        SettingsResource.receive({
            language: 'EN_CA'
        });
        expect(SettingsResource.get('language')).toBe('en-ca');
    }));


    it('should fall back to more general language code', inject(function (
        SettingsResource) {

        SettingsResource.receive({
            language: 'EN_US'
        });
        expect(SettingsResource.get('language')).toBe('en');
    }));


});