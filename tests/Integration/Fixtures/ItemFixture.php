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


use OCA\News\Db\Item;

class ItemFixture extends Item {
    use Fixture;

    public function __construct(array $defaults=[])  {
        parent::__construct();
        $defaults = array_merge([
            'url' => 'http://google.de',
            'title' => 'title',
            'author' => 'my author',
            'pubDate' => 2323,
            'body' => 'this is a body',
            'enclosureMime' => 'video/mpeg',
            'enclosureLink' => 'http://google.de/web.webm',
            'feedId' => 0,
            'status' => 2,
            'lastModified' => 113,
            'rtl' => false,
        ], $defaults);

        if (!array_key_exists('guid', $defaults)) {
            $defaults['guid'] = $defaults['title'];
        }

        if (!array_key_exists('guidHash', $defaults)) {
            $defaults['guidHash'] = $defaults['guid'];
        }

        $this->fillDefaults($defaults);
        $this->generateSearchIndex();
    }

}
