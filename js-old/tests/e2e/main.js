/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */
describe('news page', function () {
    'use strict';

    beforeEach(function () {
        browser.ignoreSynchronization = true;
        browser.waitForAngular();
    });

    it('should go to the news page', function () {
        browser.getTitle().then(function (title) {
            expect(title).toContain('News');
        });
    });


    it('should show the first run page', function () {
        //var firstRun = browser.findElement(By.id('first-run'));
        //firstRun.findElement(By.tagName('h1')).then(function (greeting) {
        //expect(greeting.getText()).toBe('Welcome to the ownCloud News app!');
        //});

        //expect(firstRun.isDisplayed()).toBe(true);
    });

});