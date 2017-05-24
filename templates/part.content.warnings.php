<?php if ($_['warnings']['improperlyConfiguredCron']) { ?>
    <news-instant-notification id="cron-warning">
        <p><?php p($l->t('Ajax or Web cron mode detected! Your feeds will not be updated!')); ?></p>
        <ul>
            <li>
                <a href="https://docs.nextcloud.org/server/9/admin_manual/configuration_server/background_jobs_configuration.html#cron"
                   target="_blank"
                   rel="noreferrer">
                    <?php
                    p($l->t('How to set up the operating system cron'));
                    ?>
                </a>
            </li>
            <li>
                <a href="https://github.com/nextcloud/news-updater"
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

<?php if ($_['warnings']['incorrectDbCharset']) { ?>
    <news-instant-notification id="cron-warning">
        <p><?php p($l->t('Incorrect MySql/MariaDb database charset detected!')); ?></p>
        <ul>
            <li>
                <a href="https://dba.stackexchange.com/a/21684"
                   target="_blank"
                   rel="noreferrer">
                    <?php
                    p($l->t('Learn on how to convert your database to utf8mb4'));
                    ?>
                </a>
            </li>
        </ul>
    </news-instant-notification>
<?php }; ?>