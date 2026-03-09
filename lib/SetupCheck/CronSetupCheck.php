<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

declare(strict_types=1);

namespace OCA\News\SetupCheck;

use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCP\IL10N;
use OCP\SetupCheck\ISetupCheck;
use OCP\SetupCheck\SetupResult;

class CronSetupCheck implements ISetupCheck
{
    public function __construct(
        private IL10N $l10n,
        private StatusService $statusService,
    ) {
    }

    public function getName(): string
    {
        return $this->l10n->t('Background job mode for feed updates');
    }

    public function getCategory(): string
    {
        return 'system';
    }

    public function run(): SetupResult
    {
        if ($this->statusService->isCronProperlyConfigured()) {
            return SetupResult::success(
                $this->l10n->t('Feed updates will run via the system cron job.')
            );
        }

        return SetupResult::warning(
            $this->l10n->t('Ajax or webcron mode detected! Your feeds will not be updated!'),
            'https://docs.nextcloud.org/server/latest/admin_manual/configuration_server/background_jobs_configuration.html#cron'
        );
    }
}
