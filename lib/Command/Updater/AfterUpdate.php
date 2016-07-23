<?php
/**
 * ownCloud - News
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

class AfterUpdate extends Command {
    private $updater;

    public function __construct(Updater $updater) {
        parent::__construct();
        $this->updater = $updater;
    }

    protected function configure() {
        $this->setName('news:updater:after-update')
            ->setDescription('This is used to clean up the database. It ' .
                             'removes old read articles which are not starred');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->updater->afterUpdate();
    }

}
