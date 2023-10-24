/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('Publisher', function () {
    'use strict';

    beforeEach(module('News'));

    it('should should publish on all possible channels',
    inject(function (Publisher) {
        var obj = {
            receive: jasmine.createSpy('receive')
        };

        Publisher.subscribe(obj).toChannels(['test']);

        Publisher.publishAll({
            test: 'tom'
        });

        expect(obj.receive).toHaveBeenCalledWith('tom', 'test');
    }));


    it('should should publish on all possible channels',
    inject(function (Publisher) {
        var obj = {
            receive: jasmine.createSpy('receive')
        };

        Publisher.subscribe(obj).toChannels(['test', 'tiny']);

        Publisher.publishAll({
            tiny: 'tom'
        });

        expect(obj.receive).toHaveBeenCalledWith('tom', 'tiny');
    }));


    it('should not broadcast not subscribed channels',
    inject(function (Publisher) {
        Publisher.publishAll({
            test: 'tom'
        });
    }));

});