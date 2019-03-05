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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is used for generating a JSON config section for a feed by executing:
 * ./occ news:generate-explore www.feed.com
 */
class ExploreGenerator extends Command
{
    /**
     * Feed and favicon fetcher.
     */
    protected $reader;
    protected $favicon;

    /**
     * Set up class.
     *
     * @param FeedIo  $reader  Feed reader
     * @param Favicon $favicon Favicon fetcher
     */
    public function __construct(FeedIo $reader, Favicon $favicon)
    {
        $this->reader  = $reader;
        $this->favicon = $favicon;
        parent::__construct();
    }

    protected function configure()
    {
        $result = [
            'title'       => 'Feed - Title',
            'favicon'     => 'www.web.com/favicon.ico',
            'url'         => 'www.web.com',
            'feed'        => 'www.web.com/rss.xml',
            'description' => 'description is here',
            'votes'       => 100,
        ];

        $this->setName('news:generate-explore')
            ->setDescription(
                'Prints a JSON string which represents the given ' .
                'feed URL and votes, e.g.: ' . json_encode($result)
            )
            ->addArgument('feed', InputArgument::REQUIRED, 'Feed to parse')
            ->addOption('votes', null, InputOption::VALUE_OPTIONAL, 'Votes for the feed, defaults to 100');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url   = $input->getArgument('feed');
        $votes = $input->getOption('votes');
        if (!$votes) {
            $votes = 100;
        }

        try {
            $resource = $this->reader->read($url);
            $feed = $resource->getFeed();
            $result = [
                'title'       => $feed->getTitle(),
                'favicon'     => $this->favicon->get($feed->getLink()),
                'url'         => $feed->getLink(),
                'feed'        => $url,
                'description' => $feed->getDescription(),
                'votes'       => $votes,
            ];

            $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
        } catch (\Throwable $ex) {
            $output->writeln('<error>Failed to fetch feed info:</error>');
            $output->writeln($ex->getMessage());
            return 1;
        }
    }

}
