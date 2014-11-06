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


    private function deleteReadOlderThanThreshold() {
        $this->itemMapper->deleteReadOlderThanThreshold(1);

        $this->itemMapper->find($this->items['del3']->getId(), $this->userId);
        $this->itemMapper->find($this->items['del4']->getId(), $this->userId);
    }


    public function testDeleteOlderThanThresholdOne() {
        $this->deleteReadOlderThanThreshold();

        $this->setExpectedException(
            'OCP\AppFramework\Db\DoesNotExistException');
        $this->itemMapper->find($this->items['del1']->getId(), $this->userId);
    }


    public function testDeleteOlderThanThresholdTwo() {
        $this->deleteReadOlderThanThreshold();

        $this->setExpectedException(
            'OCP\AppFramework\Db\DoesNotExistException');
        $this->itemMapper->find($this->items['del2']->getId(), $this->userId);
    }


}