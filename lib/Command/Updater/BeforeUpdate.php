<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

namespace OCA\News\Command\Updater;

use OCA\News\Service\UpdaterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BeforeUpdate extends Command
{
    /**
     * @var UpdaterService Updater
     */
    private $updaterService;

    public function __construct(UpdaterService $updater)
    {
        parent::__construct();
        $this->updaterService = $updater;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:updater:before-update')
            ->setDescription(
                'This is used to clean up the database. It ' .
                             'deletes folders and feeds that are marked for ' .
                'deletion'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->updaterService->beforeUpdate();

        return 0;
    }
}
