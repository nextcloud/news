/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

 /*jshint unused:false*/
app.service('SettingsResource', function ($http, BASE_URL) {
    'use strict';

    this.settings = {
        language: 'en',
        showAll: null,
        compact: false,
        oldestFirst: null,
        preventReadOnScroll: false,
        compactExpand: false,
        exploreUrl: ''
    };
    this.defaultLanguageCode = 'en';
    this.supportedLanguageCodes = [
        'ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en', 'en-ca',
        'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he', 'hi',
        'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne',
        'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr',
        'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw'
    ];

    this.getSupportedLanguageCodes = function () {
        return this.supportedLanguageCodes;
    };

    this.receive = function (data) {
        var self = this;
        Object.keys(data).forEach(function (key) {
            var value = data[key];

            if (key === 'language') {
                value = self.processLanguageCode(value);
            }

            self.settings[key] = value;
        });
    };

    this.get = function (key) {
        return this.settings[key];
    };

    this.set = function (key, value) {
        this.settings[key] = value;

        return $http({
            url: BASE_URL + '/settings',
            method: 'PUT',
            data: {
                language: this.settings.language,
                showAll: this.settings.showAll,
                compact: this.settings.compact,
                oldestFirst: this.settings.oldestFirst,
                compactExpand: this.settings.compactExpand,
                preventReadOnScroll: this.settings.preventReadOnScroll
            }
        });
    };

    this.processLanguageCode = function (languageCode) {
        languageCode = languageCode.replace('_', '-').toLowerCase();

        if (this.supportedLanguageCodes.indexOf(languageCode) < 0) {
            languageCode = languageCode.split('-')[0];
        }

        if (this.supportedLanguageCodes.indexOf(languageCode) < 0) {
            languageCode = this.defaultLanguageCode;
        }

        return languageCode;
    };

});
