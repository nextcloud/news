<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Clayton <claytonlin1110@gmail.com>
 */

declare(strict_types=1);

namespace OCA\News\SetupCheck;

use OCA\News\Service\StatusService;
use OCP\IL10N;
use OCP\SetupCheck\ISetupCheck;
use OCP\SetupCheck\SetupResult;

class CronSetupCheck implements ISetupCheck
{
    public function __construct(
        private readonly IL10N $l10n,
        private readonly StatusService $statusService,
    ) {
    }

    public function getName(): string
    {
        return $this->l10n->t('Background job mode for feed updates');
    }

    public function getCategory(): string
    {
        return Application::NAME;
    }

    public function run(): SetupResult
    {
        if ($this->statusService->isCronProperlyConfigured()) {
            return SetupResult::success(
                $this->l10n->t('Feed update method is correctly configured.')
            );
        }

        return SetupResult::warning(
            $this->l10n->t('Ajax or webcron mode detected! Your feeds will not be updated!'),
            'https://docs.nextcloud.org/server/latest/admin_manual/'
            . 'configuration_server/background_jobs_configuration.html#cron'
        );
    }
}
