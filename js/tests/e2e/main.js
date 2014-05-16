/**
 * ownCloud - News
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
        browser.get('http://localhost/owncloud/index.php/apps/news/');
    });

    it('should go to the news page', function () {
        browser.getTitle().then(function (title) {
            expect(title).toContain('News');
        });
    });

});