<?php
script('news', 'admin/Admin');
style('news', 'admin');
?>

<div class="section" id="news">
    <h2>News</h2>
    <div class="form-line">
        <input type="checkbox" name="news-use-cron-updates"
               <?php if ($_['useCronUpdates']) p('checked'); ?>>
        <label for="news-use-cron-updates">
            <?php p($l->t('Use ownCloud cron to for updates')); ?></label>
        <p>
            <em><?php p($l->t(
                'Disable this if you run a custom updater such as the Python ' .
                'updater included in the app.'
            )); ?></em>
        </p>
    </div>
    <div class="form-line">
        <input type="text" name="news-auto-purge-minimum-interval"
               value="<?php p($_['autoPurgeMinimumInterval']); ?>">
        <label for="news-auto-purge-minimum-interval">
            <?php p($l->t('Purge interval')); ?>
        </label>
        <p>
            <em>
            <?php p($l->t(
                'Minimum amount of seconds after deleted feeds and folders are ' .
                'removed from the database. Values below 60 seconds are ignored'
            )); ?></em>
        </p>
    </div>
    <div class="form-line">
        <input type="text" name="news-auto-purge-count"
               value="<?php p($_['autoPurgeCount']); ?>">
        <label for="news-auto-purge-count">
            <?php p($l->t('Maximum unread count per feed')); ?>
        </label>
        <p>
            <em>
            <?php p($l->t(
                'Defines the minimum amount of articles that can be unread per ' .
                'feed before they get deleted.'
            )); ?></em>
        </p>
    </div>
    <div class="form-line">
        <input type="text" name="news-cache-duration"
               value="<?php p($_['cacheDuration']); ?>">
        <label for="news-cache-duration">
            <?php p($l->t('Cache duration')); ?>
        </label>
        <p>
            <em><?php p($l->t('Amount of seconds to cache feeds')); ?></em>
        </p>
    </div>
    <div class="form-line">
        <input type="text" name="news-feed-fetcher-timeout"
               value="<?php p($_['feedFetcherTimeout']); ?>">
        <label for="news-feed-fetcher-timeout">
            <?php p($l->t('Feed fetcher timeout')); ?>
        </label>
        <p>
            <em>
            <?php p($l->t(
                'Maximum number of seconds to wait for an RSS or Atom feed to ' .
                'load. If a feed takes longer than that number of seconds to ' .
                'update, the update will be aborted.'
            )); ?></em>
        </p>
    </div>
    <div id="news-saved-message">
        <span class="msg success"><?php p($l->t('Saved')); ?></span>
    </div>
</div>