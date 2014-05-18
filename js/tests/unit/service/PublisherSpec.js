/**
 * ownCloud - News
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

    it('should subscribe an object and publish a message', inject(function (Publisher) {
        var obj = {
            receive: jasmine.createSpy('receive')
        };
        Publisher.subscribe(obj).toChannel('test');

        Publisher.publish('tom').onChannel('test');
        expect(obj.receive).toHaveBeenCalledWith('tom');
    }));


    it('should should publish on all possible channels', inject(function (Publisher) {

        var obj = {
            receive: jasmine.createSpy('receive')
        };
        Publisher.subscribe(obj).toChannel('test');

        Publisher.publishAll({
            test: 'tom'
        });

        expect(obj.receive).toHaveBeenCalledWith('tom');

    }));

});