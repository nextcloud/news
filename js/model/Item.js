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

    Item.prototype.receive = function (value, channel) {
        switch (channel) {

        case 'newestItemId':
            this.newestItemId = value;
            break;

        case 'starred':
            this.starredCount = value;
            break;
        default:
            Model.prototype.receive.call(this, value, channel);
        }
    };

    Item.prototype.getNewestItemId = function () {
        return this.newestItemId;
    };

    Item.prototype.getStarredCount = function () {
        return this.starredCount;
    };


    return new Item();
});