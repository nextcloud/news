/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('Settings', () => {
    'use strict';

    beforeEach(module('News'));

    it('should receive default settings', inject((Settings) => {
        Settings.receive({
            'showAll': true
        });

        expect(Settings.get('showAll')).toBe(true);
    }));


    it('should set values', inject((Settings) => {
        Settings.set('showAll', true);

        expect(Settings.get('showAll')).toBe(true);
    }));

});