/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('OPMLParser', function () {
    'use strict';

    var importer;

    beforeEach(module('News', function ($provide) {
        $provide.value('BASE_URL', 'base');
        $provide.value('ITEM_BATCH_SIZE', 3);
    }));

    beforeEach(inject(function (OPMLImporter) {
        importer = OPMLImporter;
    }));


    // FIXME: tests missing
    it ('should parse the correct amount of feeds and folders', function () {

    });


});
