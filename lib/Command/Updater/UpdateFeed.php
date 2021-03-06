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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFeed extends Command
{
    /**
     * @var FeedServiceV2 Feed service
     */
    private $feedService;

    public function __construct(FeedServiceV2 $feedService)
    {
        parent::__construct();
        $this->feedService = $feedService;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:updater:update-feed')
            ->addArgument(
                'user-id',
                InputArgument::REQUIRED,
                'user id of a user, string'
            )
            ->addArgument(
                'feed-id',
                InputArgument::REQUIRED,
                'feed id, integer'
            )
            ->setDescription('Console API for updating a single user\'s feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user-id');
        $feedId = (int) $input->getArgument('feed-id');
        try {
            $feed = $this->feedService->find($userId, $feedId);
            $updated_feed = $this->feedService->fetch($feed);
        } catch (\Exception $e) {
            $output->writeln(
                '<error>Could not update feed with id ' . $feedId .
                             ' and user ' . $userId . ': ' . $e->getMessage() .
                '</error> '
            );
            return 1;
        }

        if ($updated_feed->getUpdateErrorCount() !== 0) {
            $output->writeln($updated_feed->getLastUpdateError());
            return 255;
        }

        return 0;
    }
}
