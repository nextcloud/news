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

    var self = this;
    this.channels = {};

    this.subscribe = function (object) {
        return {
            toChannels: function () {
                var counter,
                    channel;
                for (counter = 0; counter < arguments.length; counter += 1) {
                    channel = arguments[counter];
                    self.channels[channel] = self.channels[channel] || [];
                    self.channels[channel].push(object);
                }
            }
        };
    };

    this.publishAll = function (data) {
        var channel,
            counter;

        for (channel in data) {
            if (data.hasOwnProperty(channel) && this.channels[channel] !== undefined) {
                for (counter = 0; counter < this.channels[channel].length; counter += 1) {
                    this.channels[channel][counter].receive(data[channel], channel);
                }
            }
        }
    };

});