<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sean Molenaar <sean@seanmolenaar.eu>
 * @copyright Sean Molenaar 2019
 */
namespace OCA\News\Command;

use FeedIo\FeedIo;
use Favicon\Favicon;

use HTMLPurifier;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Service\FeedService;
use OCA\News\Utility\Time;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is used for debugging feed data:
 * ./occ news:show-feed www.feed.com
 */
class ShowFeed extends Command
{
    /**
     * Feed and favicon fetcher.
     */
    protected $feedFetcher;

    /**
     * Set up class.
     *
     * @param Fetcher  $feedFetcher  Feed reader
     */
    public function __construct(Fetcher $feedFetcher)
    {
        parent::__construct();
        $this->feedFetcher  = $feedFetcher;
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('news:show-feed')
            ->setDescription('Prints a JSON string which represents the given feed as it would be in the DB.')
            ->addArgument('feed', InputArgument::REQUIRED, 'Feed to parse')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'Username for the feed')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Password for the feed')
            ->addOption('full-text', 'f', InputOption::VALUE_NONE, 'Usa a scraper to get full text');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url   = $input->getArgument('feed');
        $user = $input->getOption('user');
        $password = $input->getOption('password');
        $fullTextEnabled = (bool) $input->getOption('full-text');

        try {
            list($feed, $items) = $this->feedFetcher->fetch($url, true, null, $fullTextEnabled, $user, $password);
            $output->writeln("Feed: " . json_encode($feed, JSON_PRETTY_PRINT));
            $output->writeln("Items: " . json_encode($items, JSON_PRETTY_PRINT));
            return 0;
        } catch (\Throwable $ex) {
            $output->writeln('<error>Failed to fetch feed info:</error>');
            $output->writeln($ex->getMessage());
            return 1;
        }
    }
}
