/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

/**
 * This prefills the add feed section if an external link has ?subsribe_to
 * filled out
 */
(function (document, url, $, undefined) {
    'use strict';

    $(document).ready(function () {
        var subscription = url('?subscribe_to');

        if (subscription) {
            $('#new-feed').show();

            var input = $('input[ng-model="Navigation.feed.url"]');
            input.val(subscription);

            // hacky way to focus because initial loading of a feed
            // steals the focus
            setTimeout(function() {
                input.focus();
            }, 1000);
        }
    });

})(document, url, $);

