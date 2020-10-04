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

use OCA\News\Service\ItemServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AfterUpdate extends Command
{
    /**
     * @var ItemServiceV2
     */
    private $itemService;

    /**
     * AfterUpdate constructor.
     *
     * @param ItemServiceV2 $itemService
     */
    public function __construct(ItemServiceV2 $itemService)
    {
        parent::__construct();
        $this->itemService = $itemService;
    }

    protected function configure()
    {
        $this->setName('news:updater:after-update')
            ->setDescription('removes old read articles which are not starred')
            ->addArgument('purge_count', InputArgument::OPTIONAL, 'The amount of items to purge');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $input->getArgument('purge_count');

        echo $this->itemService->purgeOverThreshold($count);

        return 0;
    }
}
