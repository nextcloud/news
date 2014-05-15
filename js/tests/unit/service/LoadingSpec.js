/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
describe('Loading', function () {
    'use strict';

    beforeEach(module('News'));

    it('should be not load by default', inject(function (Loading) {
        expect(Loading.isLoading('global')).toBe(false);
        expect(Loading.isLoading('content')).toBe(false);
    }));

    it('should set loading', inject(function (Loading) {
        Loading.setLoading('global', true);
        expect(Loading.isLoading('global')).toBe(true);
    }));

});