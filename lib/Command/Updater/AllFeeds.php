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

use OCA\News\Service\FeedServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AllFeeds
 *
 * @deprecated use news:feed:list instead
 * @package OCA\News\Command\Updater
 */
class AllFeeds extends Command
{
    /**
     * @var FeedServiceV2 Feed service
     */
    private $feedService;

    /**
     * AllFeeds constructor.
     *
     * @param FeedServiceV2 $feedService
     */
    public function __construct(FeedServiceV2 $feedService)
    {
        parent::__construct();
        $this->feedService = $feedService;
    }

    protected function configure()
    {
        $json = '{"feeds": [{"id": 39, "userId": "john"}, // etc ]}';

        $this->setName('news:updater:all-feeds')
            ->setDescription(
                'DEPRECATED: use news:feed:list instead.' . PHP_EOL .
                'Prints a JSON string which contains all feed ' .
                'ids and user ids, e.g.: ' . $json
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feeds = $this->feedService->findAll();
        $result = ['feeds' => []];

        foreach ($feeds as $feed) {
            $result['feeds'][] = [
                'id' => $feed->getId(),
                'userId' => $feed->getUserId(),
                'folderId' => $feed->getFolderId(),
            ];
        }

        $output->write(json_encode($result));
    }
}
