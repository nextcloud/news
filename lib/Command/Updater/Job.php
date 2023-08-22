<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Command\Updater;

use DateTime;
use OCP\Util;
use OCA\News\Service\StatusService;
use OCA\News\Service\UpdaterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Job extends Command
{
    /**
     * @var StatusService Status service
     */
    private $statusService;

    /**
     * @var UpdaterService Update service
     */
    private $updaterService;

    public function __construct(StatusService $statusService, UpdaterService $updaterService)
    {
        parent::__construct();
        $this->statusService = $statusService;
        $this->updaterService = $updaterService;
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
            ->setDescription('Console API for checking the update job status and to reset it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reset = (bool) $input->getOption('reset');

        [$major, $minor, $micro] = Util::getVersion();
        
        if ($major < 26) {
            $output->writeln("Error: This only works with Nextcloud 26 or newer.");
            return 1;
        }
        $output->writeln("Checking update Status");
        $date = new DateTime();
        $date->setTimestamp($this->statusService->getUpdateTime());
        $output->writeln("Last Execution was ".$date->format('Y-m-d H:i:s e'));

        if ($reset) {
            $output->writeln("Attempting to reset the job.");
            $this->updaterService->reset();
            $output->writeln("Done, job should execute on next schedule.");
        }
        return 0;
    }
}
