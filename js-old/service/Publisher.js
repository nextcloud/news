/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

/*jshint undef:false*/
app.service('Publisher', function () {
    'use strict';

    this.channels = {};

    this.subscribe = function (obj) {
        var self = this;

        return {
            toChannels: function (channels) {
                channels.forEach(function (channel) {
                    self.channels[channel] = self.channels[channel] || [];
                    self.channels[channel].push(obj);
                });
            }
        };

    };

    this.publishAll = function (data) {
        var self = this;

        Object.keys(data).forEach(function (channel) {
            var listeners = self.channels[channel];
            if (listeners !== undefined) {
                listeners.forEach(function (listener) {
                    listener.receive(data[channel], channel);
                });
            }
        });
    };

});