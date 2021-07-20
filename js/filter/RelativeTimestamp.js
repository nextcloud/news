/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alec Kojaev <alec@kojaev.name>
 * @copyright Alec Kojaev 2021
 */
app.filter('relativeTimestamp', ['SettingsResource', function (SettingsResource) {
    'use strict';

    const languageCode = SettingsResource.get('language');
    const relFormat = Intl.RelativeTimeFormat ?
        new Intl.RelativeTimeFormat(languageCode, { numeric: 'auto' }) : null;
    const maxRelDistance = 90*86400*1000;
    const relLimits = [
        [ 7*86400*1000, 'week'   ],
        [   86400*1000, 'day'    ],
        [    3600*1000, 'hour'   ],
        [      60*1000, 'minute' ],
        [       1*1000, 'second' ]
    ];
    const absLimits = [
        [ 7*86400*1000, { hour: '2-digit', minute: '2-digit', dayPeriod: 'narrow',
                          year: 'numeric', month: 'short', day: 'numeric' } ],
        [   43200*1000, { hour: '2-digit', minute: '2-digit', dayPeriod: 'narrow',
                          weekday: 'long' } ],
        [            0, { hour: '2-digit', minute: '2-digit', dayPeriod: 'narrow' } ]
    ];

    return function (timestamp) {
        if (!Number.isFinite(timestamp)) {
            return timestamp;
        }
        const ts = new Date(timestamp);
        const dist = ts.getTime() - Date.now();
        const absDist = Math.abs(dist);
        if (relFormat && absDist < maxRelDistance) {
            for (const [ scale, unit ] of relLimits) {
                const value = Math.trunc(dist / scale);
                if (value !== 0) {
                    return relFormat.format(value, unit);
                }
            }
            // We arrive here only if distance from now is less than 1 second
            return relFormat.format(0, 'second');
        } else {
            for (const [ limit, options ] of absLimits) {
                if (absDist >= limit) {
                    return ts.toLocaleString(languageCode, options);
                }
            }
            // We shouldn't be here
            return ts.toLocaleString(languageCode, absLimits[absLimits.length - 1][1]);
        }
    };
}]);
