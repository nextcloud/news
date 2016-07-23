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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use OCA\News\Service\FeedService;


class UpdateFeed extends Command {
    private $feedService;

    public function __construct(FeedService $feedService) {
        parent::__construct();
        $this->feedService = $feedService;
    }

    protected function configure() {
        $this->setName('news:updater:update-feed')
            ->addArgument(
                'feed-id',
                InputArgument::REQUIRED,
                'feed id, integer'
            )
            ->addArgument(
                'user-id',
                InputArgument::REQUIRED,
                'user id of a user, string'
            )
            ->setDescription('Console API for updating a single user\'s feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $feedId = $input->getArgument('feed-id');
        $userId = $input->getArgument('user-id');
        try {
            $this->feedService->update($feedId, $userId);
        } catch (Exception $e) {
            $output->writeln('<error>Could not update feed with id ' . $feedId .
                             ' and user ' . $userId . ': ' . $e->getMessage() .
                             '</error> ');
        }
    }

}
