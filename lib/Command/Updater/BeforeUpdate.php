<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

namespace OCA\News\Command\Updater;

use Exception;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use \OCA\News\Utility\Updater;

class BeforeUpdate extends Command {
    private $updater;

    public function __construct(Updater $updater) {
        parent::__construct();
        $this->updater = $updater;
    }

    protected function configure() {
        $this->setName('news:updater:before-update')
            ->setDescription('This is used to clean up the database. It ' .
                             'deletes folders and feeds that are marked for ' .
                             'deletion');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->updater->beforeUpdate();
    }

}
