<?php

namespace OCA\News\Command\Config;

use OCA\News\Db\Feed;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FeedDelete extends Command
{
    /**
     * @var FeedServiceV2 service for the feeds.
     */
    protected $feedService;

    public function __construct(FeedServiceV2 $feedService)
    {
        parent::__construct(null);

        $this->feedService = $feedService;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('news:feed:delete')
            ->setDescription('Remove a feed')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to remove the feed from')
            ->addArgument('feed-id', InputArgument::REQUIRED, 'Feed ID', null);
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $input->getArgument('user-id');
        $id = $input->getArgument('feed-id');

        $this->feedService->delete($user, $id);

        return 0;
    }
}
