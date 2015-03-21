<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */

namespace OCA\News\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use OCA\News\Service\ItemService;


class GenerateSearchIndices extends Command {

    private $service;

    public function __construct(ItemService $service) {
        parent::__construct();
        $this->service = $service;
    }

    protected function configure() {
        $this->setName('news:create-search-indices')
             ->setDescription('Recreates the search indices for all items');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("\nCreating search indices, this could take a while...\n");
        $progressbar = function ($steps) use ($output) {
            return new ProgressBar($output, $steps);
        };
        $this->service->generateSearchIndicies($progressbar);
        $output->writeln("\n");
    }

}
