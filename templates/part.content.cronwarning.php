<?php if ($_['cronWarning']) { ?>
    <news-instant-notification id="cron-warning">
        <p><?php p($l->t('Ajax or webcron cron mode detected! Your feeds will ' .
            'not be updated correctly. It is recommended to either use ' .
            'the operating system cron or a custom updater.'
        )); ?>
            <ul>
                <li>
                    <a href="https://doc.owncloud.org/server/8.1/admin_manual/configuration_server/background_jobs_configuration.html#cron"
                       target="_blank">
                    <?php
                        p($l->t('How to set up the operating system cron'));
                    ?>
                    </a>
                </li>
                <li>
                    <a href="https://github.com/owncloud/news/wiki/Custom-Updater"
                       target="_blank">
                        <?php
                            p($l->t('How to set up a custom updater ' .
                                    '(faster and no possible deadlock) '
                            ));
                        ?>
                    </a>
                </li>
            </ul>
        </p>
    </news-instant-notification>
<?php }; ?>
