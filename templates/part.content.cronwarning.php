<?php if ($_['cronWarning'] === 'ajaxCron') { ?>
    <div id="cron-warning">
        <p><?php p($l->t('Ajax cron mode detected! Your feeds will ' .
            'not be updated correctly. It is recommended to either use ' .
            'the operating system cron or a custom updater.'
        )); ?>
            <ul>
                <li>
                    <a href="http://doc.owncloud.org/server/7.0/admin_manual/configuration/background_jobs.html#cron"
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
    </div>
<?php }; ?>