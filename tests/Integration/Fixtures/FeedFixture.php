<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */


namespace OCA\News\Tests\Integration\Fixtures;

use OCA\News\Db\Feed;

class FeedFixture extends Feed
{

    use Fixture;

    public function __construct(array $defaults = [])
    {
        parent::__construct();
        $defaults = array_merge(
            [
                'userId' => 'test',
                'urlHash' => 'urlHash',
                'url' => 'http://the.url.com',
                'title' => 'title',
                'faviconLink' => 'http://feed.com/favicon.ico',
                'added' => 3000,
                'folderId' => null,
                'link' => 'http://feed.com/rss',
                'preventUpdate' => false,
                'deletedAt' => 0,
                'articlesPerUpdate' => 40,
                'httpLastModified' => 10,
                'httpEtag' => '',
                'location' => 'http://feed.com/rss',
                'ordering' => 0,
                'fullTextEnabled' => false,
                'pinned' => false,
                'updateMode' => 0,
                'updateErrorCount' => 0,
                'lastUpdateError' => '',
            ],
            $defaults
        );
        unset($defaults['items']);
        $this->fillDefaults($defaults);
    }

}
