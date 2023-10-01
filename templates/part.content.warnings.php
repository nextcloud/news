<?php if ($_['warnings']['improperlyConfiguredCron']) { ?>
    <style>
        #cron-warning {
            position: absolute;
            right: 30px;
            top: 40px;
            z-index: 5;
            padding: 5px;
            background-color: var(--color-main-background);
            color: var(--color-main-text);
            box-shadow: 0 0 6px 0 var(--color-box-shadow);
            border-radius: var(--border-radius);
            display: flex;
        }

        #cron-warning a {
            color: #3a84e4;
            text-decoration: underline;
            font-size: small;
        }

        #close-cron-warning {
            padding: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        #content {
            margin-top: 0px;
        }
    </style>

    <div id="cron-warning">
        <div style="<?= $_['nc_major_version'] >= 25 ? 'padding: 12px;' : ''; ?>">
            <p><?php p($l->t('Ajax or webcron mode detected! Your feeds will not be updated!')); ?></p>
            <ul>
                <li>
                    <a href="https://docs.nextcloud.org/server/latest/admin_manual/configuration_server/background_jobs_configuration.html#cron"
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
        </div>
        <div>
            <span id="close-cron-warning">X</span>
        </div>
    </div>
<?php }; ?>
