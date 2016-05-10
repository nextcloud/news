<?php if ($_['cronWarning']) { ?>
    <news-instant-notification id="cron-warning">
        <p><?php p($l->t('Ajax or Web cron mode detected! Your feeds will not be updated!')); ?></p>
        <ul>
            <li>
                <a href="https://doc.owncloud.org/server/9.0/admin_manual/configuration_server/background_jobs_configuration.html#cron"
                   target="_blank"
                   rel="noreferrer">
                    <?php
                    p($l->t('How to set up the operating system cron'));
                    ?>
                </a>
            </li>
            <li>
                <a href="https://github.com/owncloud/news-updater"
                   target="_blank"
                   rel="noreferrer">
                    <?php
                    p($l->t('Install and set up a faster parallel updater that uses the News app\'s update API'));
                    ?>
                </a>
            </li>
        </ul>
    </news-instant-notification>
<?php }; ?>
