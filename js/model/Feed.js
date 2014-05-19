/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.factory('Feed', function (Model) {
    'use strict';

    var Feed = function () {
        Model.call(this, 'url');
    };

    Feed.prototype = Object.create(Model.prototype);

    return new Feed();
});