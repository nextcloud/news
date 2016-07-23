/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('unreadCountFormatter', function () {
    'use strict';

    var filter;

    beforeEach(module('News'));

    beforeEach(inject(function ($filter) {
        filter = $filter('unreadCountFormatter');
    }));

    it('should format the unread count', function () {
        expect(filter(999)).toBe(999);
        expect(filter(1000)).toBe('999+');
    });


});