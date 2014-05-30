/**
 * ownCloud - News
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

    this.settings = {};
    this.defaultLanguageCode = 'en';
    this.supportedLanguageCodes = [
        'ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en-ca',
        'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he', 'hi',
        'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne',
        'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr',
        'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw'
    ];

    this.receive = (data) => {
        for (let [key, value] of items(data)) {
            if (key === 'language') {
                value = this.processLanguageCode(value);
            }
            this.settings[key] = value;
        }
    };

    this.get = (key) => {
        return this.settings[key];
    };

    this.set = (key, value) => {
        this.settings[key] = value;

        let data = {};
        data[key] = value;

        return $http({
                url: `${BASE_URL}/settings`,
                method: 'POST',
                data: data
            });
    };

    this.processLanguageCode = (languageCode) => {
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