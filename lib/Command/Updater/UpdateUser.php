<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Command\Updater;

use Exception;
use OCA\News\Service\FeedServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
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
        $this->setName('news:updater:update-user')
            ->addArgument(
                'user-id',
                InputArgument::REQUIRED,
                'user id of a user, string'
            )
            ->setDescription('Console API for updating a single user\'s feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user-id');
        $feeds = $this->feedService->findAllForUser($userId);
        $updateErrors = false;
        foreach ($feeds as $feed) {
            try {
                $updated_feed = $this->feedService->fetch($feed);
                if ($updated_feed->getUpdateErrorCount() !== 0) {
                    $output->writeln($updated_feed->getLastUpdateError());
                    $updateErrors = true;
                }
            } catch (Exception $e) {
                $output->writeln(
                    '<error>Could not update feed with id ' . $feed->getId() .
                    '.  ' . $e->getMessage() . '</error> '
                );
                return 1;
            }
        }
        if ($updateErrors) {
            return 255;
        }
        return 0;
    }
}
