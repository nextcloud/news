/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('Item', function (Model) {
    'use strict';

    var Item = function () {
        Model.call(this, 'id');
    };

    Item.prototype = Object.create(Model.prototype);

    return new Item();
});