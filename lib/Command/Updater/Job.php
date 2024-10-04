<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Command\Updater;

use DateTime;
use DateInterval;
use OCP\IAppConfig;
use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCA\News\Service\UpdaterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Job extends Command
{
    public function __construct(
        private IAppConfig $config,
        private StatusService $statusService,
        private UpdaterService $updaterService
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:updater:job')
            ->addOption(
                'reset',
                null,
                InputOption::VALUE_NONE,
                'If the job should be reset, warning this might lead to issues.'
            )
            ->addOption(
                'check-elapsed',
                null,
                InputOption::VALUE_NONE,
                'Check if the last job execution was too long ago. Return exit code 2 if so.'
            )
            ->setDescription('Console API for checking the update job status and to reset it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reset = (bool) $input->getOption('reset');
        $checkElapsed = (bool) $input->getOption('check-elapsed');

        $output->writeln("Checking update Status");
        $date = new DateTime();
        $date->setTimestamp($this->statusService->getUpdateTime());
        $now = new DateTime('now');
        $elapsedInterval = $now->diff($date);
        $output->writeln("Last Execution was ".$date->format('Y-m-d H:i:s e'));
        if ($checkElapsed) {
            $output->writeln($elapsedInterval->format('%h hours, %i minutes, %s seconds ago'));
        }

        if ($reset) {
            $output->writeln("Attempting to reset the job.");
            $this->updaterService->reset();
            $output->writeln("Done, job should execute on next schedule.");
            return 0;
        }

        if ($checkElapsed) {
            $updateInterval = $this->config->getValueString(
                Application::NAME,
                'updateInterval',
                Application::DEFAULT_SETTINGS['updateInterval']
            );
            $threshold = ($updateInterval * 2) + 900;
            $elapsedSeconds = $now->getTimestamp() - $date->getTimestamp();
            if ($elapsedSeconds > $threshold) {
                $output->writeln(
                    "Something is wrong with the news cronjob, execution delay exceeded the configured interval."
                );
                return 2;
            }
        }

        return 0;
    }
}
