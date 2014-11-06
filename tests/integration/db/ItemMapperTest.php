<?php

namespace OCA\News\Db;

use \OCA\News\AppInfo\Application;
use \OCA\News\Tests\Integration\NewsIntegrationTest;

class ItemMapperTest extends NewsIntegrationTest {

    private $container;
    private $itemMapper;

    protected function setUp() {
        parent::setUp();
        $app = new Application();
        $this->container = $app->getContainer();
        $this->itemMapper = $this->container->query('ItemMapper');
        $this->feedMapper = $this->container->query('FeedMapper');
        $this->folderMapper = $this->container->query('FolderMapper');
    }


    private function setupFeedAndFolder($feedOptions=[], $folderOptions=[]) {
        $folderDefault = [
            'id' => 5,
            'userId' => $this->userId,
            'name' => 'a folder',
            'parentId' => 0
        ];
        $feedDefault = [
            'id' => 3,
            'userId' => $this->userId,
            'url' => 'http://google.com',
            'title' => 'le feed',
            'folderId' => 5
        ];

        $folderDefault = array_merge($folderDefault, $folderOptions);
        $feedDefault = array_merge($feedDefault, $feedOptions);

        $feed = new Feed();
        foreach ($feedDefault as $key => $value) {
            $method = 'set' . ucfirst($key);
            $feed->$method($value);
        }
        $this->feedMapper->insert($feed);

        $folder = new Folder();
        foreach ($folderDefault as $key => $value) {
            $method = 'set' . ucfirst($key);
            $folder->$method($value);
        }
        $this->folderMapper->insert($folder);

    }


    public function testInsert() {
        $this->setupFeedAndFolder();

        $item = new Item();
        $item->setTitle('my title');
        $item->setGuid('test');
        $item->setFeedId(3);
        $item->setUnread();

        $created = $this->itemMapper->insert($item);

        $fetched = $this->itemMapper->find($created->getId(), $this->userId);

        $this->assertEquals($item->getTitle(), $fetched->getTitle());
        $this->assertEquals($item->getGuid(), $fetched->getGuid());
        $this->assertEquals($item->getGuidHash(), $fetched->getGuidHash());
        $this->assertEquals($item->getFeedId(), $fetched->getFeedId());
        $this->assertEquals($item->isRead(), $fetched->isRead());
    }

}