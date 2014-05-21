/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('AppController', () => {
    'use strict';

    let controller;

    beforeEach(module('News'));

    beforeEach(inject(($controller) => {
        controller = $controller('AppController');
    }));


    it('should expose Loading', inject((Loading) => {
        expect(controller.loading).toBe(Loading);
    }));


    it('should expose set firstrun if no feeds and folders', inject(() => {
        expect(controller.isFirstRun()).toBe(true);
    }));


    it('should expose set firstrun if feeds', inject((FeedResource) => {
        FeedResource.add({url: 'test'});

        expect(controller.isFirstRun()).toBe(false);
    }));


    it('should expose set firstrun if folders', inject((FolderResource) => {
        FolderResource.add({name: 'test'});

        expect(controller.isFirstRun()).toBe(false);
    }));

});