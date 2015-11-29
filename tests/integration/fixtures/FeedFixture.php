<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */


namespace OCA\News\Tests\Integration\Fixtures;


use OCA\News\Db\Feed;

class FeedFixture extends Feed {

    use Fixture;

    public function __construct(array $defaults=[])  {
        parent::__construct();
        $defaults = array_combine([
            'userId' => 'test',
            'urlHash' => 'urlHash',
            'url' => 'http://the.url.com',
            'title' => 'title',
            'faviconLink' => 'http://the.faviconLink.com',
            'added' => 9,
            'folderId' => 0,
            'unreadCount' => 0,
            'link' => 'http://thelink.com',
            'preventUpdate' => false,
            'deletedAt' => 0,
            'articlesPerUpdate' => 50,
            'lastModified' => 10,
            'etag' => '',
            'location' => 'http://thefeed.com',
            'ordering' => 0,
            'fullTextEnabled' => false,
            'pinned' => false,
            'updateMode' => 0,
            'updateErrorCount' => 0,
            'lastUpdateError' => 'lastUpdateError',
        ], $defaults);
        $this->fillDefaults($defaults);
    }

}