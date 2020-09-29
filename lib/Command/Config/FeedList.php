<?php

namespace OCA\News\Command\Config;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Service\FeedServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FeedList extends Command
{
    use ApiPayloadTrait;

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
        $this->setName('news:feed:list')
            ->setDescription('List all feeds')
            ->addArgument('userID', InputArgument::REQUIRED, 'User to list the feeds for')
            ->addOption('recursive', null, InputOption::VALUE_NONE, 'Fetch the feed recursively');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getArgument('userID');
        $recursive = $input->getOption('recursive');

        if ($recursive !== false) {
            $feeds = $this->feedService->findAllForUserRecursive($user);
        } else {
            $feeds = $this->feedService->findAllForUser($user);
        }

        $output->writeln(json_encode($this->serialize($feeds), JSON_PRETTY_PRINT));

        return 0;
    }
}
