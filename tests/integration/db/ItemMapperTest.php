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


    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotFoundWhenDeletedFeed() {
        $id = $this->items['not found feed']->getId();
        $this->itemMapper->find($id, $this->userId);
    }


    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotFoundWhenDeletedFolder() {
        $id = $this->items['not found folder']->getId();
        $this->itemMapper->find($id, $this->userId);
    }


    public function testDeleteOlderThanThreshold() {
        $this->itemMapper->deleteReadOlderThanThreshold(1);
        $item1 = $this->items['del1'];
        $item2 = $this->items['del2'];
        $item3 = $this->items['del3'];
        $item4 = $this->items['del4'];

        $this->itemMapper->find($item3->getId(), $this->userId);
        $this->itemMapper->find($item4->getId(), $this->userId);

        //$this->setExpectedException(
        //    'OCP\AppFramework\Db\DoesNotExistException');
        $this->itemMapper->find($item1->getId(), $this->userId);
        $this->itemMapper->find($item2->getId(), $this->userId);
    }


}