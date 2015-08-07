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
 
namespace OCA\News\Db;

use \OCA\News\Tests\Integration\NewsIntegrationTest;

class ItemMapperTest extends NewsIntegrationTest {


    public function testFind() {
        $feedId = $this->feeds['first feed']->getId();

        $item = new Item();
        $item->setTitle('my title thats long');
        $item->setGuid('a doner');
        $item->setGuidHash('a doner');
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

        $this->itemMapper->find($this->items['a title1']->getId(),
                                $this->userId);
        $this->itemMapper->find($this->items['a title2']->getId(),
                                $this->userId);
        $this->itemMapper->find($this->items['a title3']->getId(),
                                $this->userId);
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


    public function testStarredCount () {
        $count = $this->itemMapper->starredCount($this->userId);
        $this->assertEquals(2, $count);
    }


    public function testReadAll () {
        $this->itemMapper->readAll(PHP_INT_MAX, 10, $this->userId);

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->userId
        );

        $this->assertEquals(0, count($items));

        $item = $this->items['a title1'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->items['a title3'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->items['a title9'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());
    }


    public function testReadFolder () {
        $folderId = $this->folders['first folder']->getId();
        $this->itemMapper->readFolder(
            $folderId, PHP_INT_MAX, 10, $this->userId
        );

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->userId
        );

        $this->assertEquals(1, count($items));

        $item = $this->items['a title1'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->items['a title3'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->items['a title9'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertTrue($item->isUnread());
    }


    public function testReadFeed () {
        $feedId = $this->feeds['third feed']->getId();
        $this->itemMapper->readFeed(
            $feedId, PHP_INT_MAX, 10, $this->userId
        );

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->userId
        );

        $this->assertEquals(2, count($items));

        $item = $this->items['a title9'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->items['a title3'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);
        $this->assertTrue($item->isUnread());


        $item = $this->items['a title1'];
        $item = $this->itemMapper->find($item->getId(), $this->userId);
        $this->assertTrue($item->isUnread());
    }


    public function testDeleteUser () {
        $this->itemMapper->deleteUser($this->userId);
        $id = $this->itemMapper->getNewestItemId();

        $this->assertEquals(0, $id);
    }


    public function testGetNewestItemId () {
        $id = $this->itemMapper->getNewestItemId($this->userId);

        $item = $this->items['no folder'];
        $this->assertEquals($item->getId(), $id);
    }


    public function testFindByGuidHash () {
        $item = $this->items['no folder'];

        $fetchedItem = $this->itemMapper->findByGuidHash(
            'no folder', $item->getFeedId(), $this->userId
        );

        $this->assertEquals($item->getId(), $fetchedItem->getId());
    }


    public function testFindAllUnreadOrStarred () {
        $items = $this->itemMapper->findAllUnreadOrStarred($this->userId);
        $this->assertEquals(4, count($items));
    }


    /* TBD
    public function testFindAllFolder () {

    }


    public function testFindAllFeed () {

    }


    public function testFindAllNew () {

    }


    public function testFindAllNewFolder () {

    }


    public function testFindAllNewFeed () {

    }

    */
}
