<?php

namespace OCA\News\Db;

use \OCA\News\Tests\Integration\NewsIntegrationTest;

class ItemMapperTest extends NewsIntegrationTest {


    public function testFind() {
        $feedId = $this->feeds['first feed']->getId();

        $item = new Item();
        $item->setTitle('my title thats long');
        $item->setGuid('a doner');
        $item->setFeedId($feedId);
        $item->setUnread();
        $item->setBody('Döner');

        $created = $this->itemMapper->insert($item);
        $fetched = $this->itemMapper->find($created->getId(), $this->userId);

        $this->assertEquals($item->getTitle(), $fetched->getTitle());
        $this->assertEquals($item->getGuid(), $fetched->getGuid());
        $this->assertEquals($item->getGuidHash(), $fetched->getGuidHash());
        $this->assertEquals($item->getFeedId(), $fetched->getFeedId());
        $this->assertEquals($item->isRead(), $fetched->isRead());
        $this->assertEquals('Döner', $fetched->getBody());
    }

}