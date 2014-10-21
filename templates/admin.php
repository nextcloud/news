<?php
script('news', 'admin/Admin');
style('news', 'admin');
?>

<div class="section" id="news">
    <h2>News</h2>
    <div class="form-line">
        <p><input type="checkbox" name="news-use-cron-updates"
               <?php if ($_['useCronUpdates']) p('checked'); ?>>
            <label for="news-use-cron-updates">
                <?php p($l->t('Use ownCloud cron to for updates')); ?>
            </label>
        </p>
        <p>
            <em><?php p($l->t(
                'Disable this if you run a custom updater such as the Python ' .
                'updater included in the app'
            )); ?></em>
        </p>
    </div>
    <div class="form-line">
        <p>
            <label for="news-auto-purge-minimum-interval">
                <?php p($l->t('Purge interval')); ?></p>
            </label>
        <p>
            <em>
            <?php p($l->t(
                'Minimum amount of seconds after deleted feeds and folders ' .
                'are removed from the database; values below 60 seconds are ' .
                'ignored'
            )); ?></em>
        </p>
        <p><input type="text" name="news-auto-purge-minimum-interval"
               value="<?php p($_['autoPurgeMinimumInterval']); ?>"></p>
    </div>
    <div class="form-line">
        <p>
            <label for="news-auto-purge-count">
                <?php p($l->t('Maximum read count per feed')); ?>
            </label>
        </p>
        <p>
            <em>
            <?php p($l->t(
                'Defines the maximum amount of articles that can be read per ' .
                "feed which won't be deleted by the cleanup job; ".
                'if old articles reappear after being read, increase ' .
                'this value'
            )); ?></em>
        </p>
        <p><input type="text" name="news-auto-purge-count"
               value="<?php p($_['autoPurgeCount']); ?>"></p>
    </div>
    <div class="form-line">
        <p>
            <label for="news-max-redirects">
                <?php p($l->t('Maximum redirects')); ?>
            </label>
        </p>
        <p>
            <em>
                <?php p($l->t(
                    'How many redirects the feed fetcher should follow'
                )); ?>
            </em>
        </p>
        <p><input type="text" name="news-max-redirects"
               value="<?php p($_['maxRedirects']); ?>"></p>
    </div>
    <div class="form-line">
        <p>
            <label for="news-feed-fetcher-timeout">
                <?php p($l->t('Feed fetcher timeout')); ?>
            </label>
        </p>
        <p>
            <em>
            <?php p($l->t(
                'Maximum number of seconds to wait for an RSS or Atom feed ' .
                'to load; if it takes longer the update will be aborted.'
            )); ?></em>
        </p>
        <p><input type="text" name="news-feed-fetcher-timeout"
               value="<?php p($_['feedFetcherTimeout']); ?>"></p>
    </div>
    <div id="news-saved-message">
        <span class="msg success"><?php p($l->t('Saved')); ?></span>
    </div>
</div>