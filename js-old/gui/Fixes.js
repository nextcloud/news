/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

/**
 * Various fixes
 */
(function (window, document) {
    'use strict';

    // If F5 is used to reload the page in Firefox, the content will sometimes
    // be scrolled back to the position where it was before the reload which
    // will cause new articles being marked as read
    window.addEventListener('beforeunload', function () {
        var content = document.querySelector('#app-content');
        content.scrollTo(0, 0);
    });

})(window, document);