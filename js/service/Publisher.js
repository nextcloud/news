/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.service('Publisher', function () {
    'use strict';

    this.channels = {};

    this.subscribe = (obj) => {
        return {
            toChannels: (...channels) => {
                for (let channel of channels) {
                    this.channels[channel] = this.channels[channel] || [];
                    this.channels[channel].push(obj);
                }
            }
        };

    };

    this.publishAll = (data) => {
        for (let channel in data) {
            if (this.channels[channel] !== undefined) {
                for (let listener of this.channels[channel]) {
                    listener.receive(data[channel], channel);
                }
            }
        }
    };

});