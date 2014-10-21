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
 * Used to update the admin settings
 */
(function (window, document, $) {
    'use strict';

    $(document).ready(function () {
        var useCronUpdatesInput =
            $('#news input[name="news-use-cron-updates"]');
        var autoPurgeMinimumIntervalInput =
            $('#news input[name="news-auto-purge-minimum-interval"]');
        var autoPurgeCountInput =
            $('#news input[name="news-auto-purge-count"]');
        var cacheDurationInput =
            $('#news input[name="news-cache-duration"]');
        var feedFetcherTimeoutInput =
            $('#news input[name="news-feed-fetcher-timeout"]');
        var savedMessage = $('#news-saved-message');

        var saved = function () {
            if (savedMessage.is(':visible')) {
                savedMessage.hide();
            }

            savedMessage.fadeIn(function () {
                setTimeout(function () {
                    savedMessage.fadeOut();
                }, 5000);
            });
        };

        var submit = function () {
            var autoPurgeMinimumInterval = autoPurgeMinimumIntervalInput.val();
            var autoPurgeCount = autoPurgeCountInput.val();
            var cacheDuration = cacheDurationInput.val();
            var feedFetcherTimeout = feedFetcherTimeoutInput.val();
            var useCronUpdates = useCronUpdatesInput.is(':checked');

            var data = {
                autoPurgeMinimumInterval:
                    parseInt(autoPurgeMinimumInterval, 10),
                autoPurgeCount: parseInt(autoPurgeCount, 10),
                cacheDuration: parseInt(cacheDuration, 10),
                feedFetcherTimeout: parseInt(feedFetcherTimeout, 10),
                useCronUpdates: useCronUpdates
            };

            var url = OC.generateUrl('/apps/news/admin');

            $.ajax({
                type: 'PUT',
                contentType: 'application/json; charset=utf-8',
                url: url,
                data: JSON.stringify(data),
                dataType: 'json'
            }).success(function (data) {
                saved();
                autoPurgeMinimumIntervalInput
                    .val(data.autoPurgeMinimumInterval);
                autoPurgeCountInput.val(data.autoPurgeCount);
                cacheDurationInput.val(data.cacheDuration);
                feedFetcherTimeoutInput.val(data.feedFetcherTimeout);
                useCronUpdatesInput.prop('checked', data.useCronUpdates);
            });

        };

        $('#news input[type="text"]').blur(submit);
        $('#news input[type="checkbox"]').change(submit);
    });


}(window, document, jQuery));