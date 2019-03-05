#!/usr/bin/env php
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
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../../../lib/base.php';

use FeedIo\FeedIo;
use Favicon\Favicon;
use OCA\News\AppInfo\Application;

$generator = new ExploreGenerator();
$generator->parse_argv($argv);
print(json_encode($generator->read(), JSON_PRETTY_PRINT));
print("\n");

/**
 * This is used for generating a JSON config section for a feed by executing:
 * php -f generate_authors.php www.feed.com
 * @deprecated Use ./occ news:generate-explore instead.
 */
class ExploreGenerator
{
    /**
     * Feed and favicon fetcher.
     */
    protected $reader;
    protected $favicon;

    /**
     * Argument data
     */
    protected $url;
    protected $votes;

    /**
     * Set up class.
     */
    public function __construct()
    {
        $app = new Application();
        $container = $app->getContainer();

        $this->reader  = $container->query(FeedIo::class);
        $this->favicon = new Favicon();
    }

    /**
     * Parse required arguments.
     * @param array $argv Arguments to the script.
     * @return void
     */
    public function parse_argv($argv = [])
    {
        if (count($argv) < 2 || count($argv) > 3)
        {
            print('Usage: php -f generate_explore http://path.com/feed [vote_count]');
            print("\n");
            exit(1);
        }

        $this->votes = (count($argv) === 3) ? $argv[2] : 100;
        $this->url = $argv[1];
    }

    /**
     * Read the provided feed and return the important data.
     * @return array Object representation of the feed
     */
    public function read()
    {
        try {
            $resource = $this->reader->read($this->url);
            $feed = $resource->getFeed();
            $result = [
                'title'       => $feed->getTitle(),
                'favicon'     => $this->favicon->get($feed->getLink()),
                'url'         => $feed->getLink(),
                'feed'        => $this->url,
                'description' => $feed->getDescription(),
                'votes'       => $this->votes,
            ];

            return $result;
        } catch (\Throwable $ex) {
            return [ 'error' => $ex->getMessage() ];
        }
    }

}
