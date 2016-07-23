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

use OCA\News\Service\FeedService;


class AllFeeds extends Command {
    private $feedService;

    public function __construct(FeedService $feedService) {
        parent::__construct();
        $this->feedService = $feedService;
    }

    protected function configure() {
        $json = '{"feeds": [{"id": 39, "userId": "john"}, // etc ]}';

        $this->setName('news:updater:all-feeds')
            ->setDescription('Prints a JSON string which contains all feed ' .
                             'ids and user ids, e.g.: ' . $json);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $feeds = $this->feedService->findAllFromAllUsers();
        $result = ['feeds' => []];

        foreach ($feeds as $feed) {
            $result['feeds'][] = [
                'id' => $feed->getId(),
                'userId' => $feed->getUserId()
            ];
        }

        print(json_encode($result));
    }

}
