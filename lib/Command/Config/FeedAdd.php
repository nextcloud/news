<?php
declare(strict_types=1);
namespace OCA\News\Command\Config;

use OCA\News\Service\FeedServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FeedAdd extends Command
{
    /**
     * @var FeedServiceV2 service for the feeds.
     */
    protected $feedService;

    /**
     * FeedAdd constructor.
     *
     * @param FeedServiceV2 $feedService
     */
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
        $this->setName('news:feed:add')
            ->setDescription('Add a feed')
            ->addArgument('userID', InputArgument::REQUIRED, 'User to add the feed for')
            ->addArgument('feed', InputArgument::REQUIRED, 'Feed to parse')
            ->addOption('folder', null, InputOption::VALUE_OPTIONAL, 'Folder ID')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Feed title')
            ->addOption('full-text', null, InputOption::VALUE_OPTIONAL, 'Scrape item URLs', false)
            ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'Basic auth username')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Basic auth password');
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
        $url = $input->getArgument('feed');
        $user = $input->getArgument('userID');
        $folder = (int) $input->getOption('folder') ?? 0;
        $title = $input->getOption('title');
        $username = $input->getOption('username');
        $full_text = $input->getOption('full-text');
        $password = $input->getOption('password');

        $feed = $this->feedService->create($user, $url, $folder, $full_text, $title, $username, $password);
        $this->feedService->fetch($feed, true);

        $output->writeln(json_encode($feed->toAPI(), JSON_PRETTY_PRINT));

        return 0;
    }
}
