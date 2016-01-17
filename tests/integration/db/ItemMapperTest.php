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

use OCA\News\Tests\Integration\Fixtures\FeedFixture;
use OCA\News\Tests\Integration\Fixtures\ItemFixture;
use OCA\News\Tests\Integration\IntegrationTest;

class ItemMapperTest extends IntegrationTest {

    public function testFind() {
        $feed = new FeedFixture();
        $feed = $this->feedMapper->insert($feed);

        $item = new ItemFixture(['feedId' => $feed->getId()]);

        $item = $this->itemMapper->insert($item);

        $fetched = $this->itemMapper->find($item->getId(), $this->user);

        $this->assertEquals($item->getTitle(), $fetched->getTitle());
    }

    /**
     * Same as whereId with easier title search
     * @param $title
     * @return mixed
     */
    private function whereTitleId($title) {
        return $this->findItemByTitle($title)->getId();
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotFoundWhenDeletedFeed() {
        $this->loadFixtures('default');

        $id = $this->whereTitleId('not found feed');
        $this->itemMapper->find($id, $this->user);
    }


    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotFoundWhenDeletedFolder() {
        $this->loadFixtures('default');


        $id = $this->whereTitleId('not found folder');
        $this->itemMapper->find($id, $this->user);
    }


    private function deleteReadOlderThanThreshold() {
        $this->loadFixtures('default');

        $this->itemMapper->deleteReadOlderThanThreshold(1);

        $this->itemMapper->find($this->whereTitleId('a title1'), $this->user);
        $this->itemMapper->find($this->whereTitleId('a title2'), $this->user);
        $this->itemMapper->find($this->whereTitleId('a title3'), $this->user);
        $this->itemMapper->find($this->whereTitleId('del3'), $this->user);
        $this->itemMapper->find($this->whereTitleId('del4'), $this->user);
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testDeleteOlderThanThresholdOne() {
        $this->loadFixtures('default');
        $id = $this->whereTitleId('del1');

        $this->deleteReadOlderThanThreshold();

        $this->itemMapper->find($id, $this->user);
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testDeleteOlderThanThresholdTwo() {
        $this->loadFixtures('default');
        $id = $this->whereTitleId('del2');

        $this->deleteReadOlderThanThreshold();

        $this->itemMapper->find($id, $this->user);
    }


    public function testStarredCount () {
        $this->loadFixtures('default');

        $count = $this->itemMapper->starredCount($this->user);
        $this->assertEquals(2, $count);
    }


    public function testReadAll () {
        $this->loadFixtures('default');

        $this->itemMapper->readAll(PHP_INT_MAX, 10, $this->user);

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->user
        );

        $this->assertEquals(0, count($items));

        $itemId = $this->whereTitleId('a title1');
        $item = $this->itemMapper->find($itemId, $this->user);

        $this->assertEquals(10, $item->getLastModified());

        $itemId = $this->whereTitleId('a title3');
        $item = $this->itemMapper->find($itemId, $this->user);

        $this->assertEquals(10, $item->getLastModified());

        $itemId = $this->whereTitleId('a title9');
        $item = $this->itemMapper->find($itemId, $this->user);

        $this->assertEquals(10, $item->getLastModified());
    }


    public function testReadFolder () {
        $this->loadFixtures('default');

        $folderId = $this->findFolderByName('first folder')->getId();
        $this->itemMapper->readFolder(
            $folderId, PHP_INT_MAX, 10, $this->user
        );

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->user
        );

        $this->assertEquals(1, count($items));

        $item = $this->findItemByTitle('a title1');
        $item = $this->itemMapper->find($item->getId(), $this->user);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->findItemByTitle('a title3');
        $item = $this->itemMapper->find($item->getId(), $this->user);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->findItemByTitle('a title9');
        $item = $this->itemMapper->find($item->getId(), $this->user);

        $this->assertTrue($item->isUnread());
    }


    public function testReadFeed () {
        $this->loadFixtures('default');

        $feedId = $this->findFeedByTitle('third feed')->getId();
        $this->itemMapper->readFeed(
            $feedId, PHP_INT_MAX, 10, $this->user
        );

        $status = StatusFlag::UNREAD;
        $items = $this->itemMapper->findAll(
            30, 0, $status, false, $this->user
        );

        $this->assertEquals(2, count($items));

        $item = $this->findItemByTitle('a title9');
        $item = $this->itemMapper->find($item->getId(), $this->user);

        $this->assertEquals(10, $item->getLastModified());

        $item = $this->findItemByTitle('a title3');
        $item = $this->itemMapper->find($item->getId(), $this->user);
        $this->assertTrue($item->isUnread());


        $item = $this->findItemByTitle('a title1');
        $item = $this->itemMapper->find($item->getId(), $this->user);
        $this->assertTrue($item->isUnread());
    }


    public function testDeleteUser () {
        $this->loadFixtures('default');

        $this->itemMapper->deleteUser($this->user);
        $id = $this->itemMapper->getNewestItemId($this->user);

        $this->assertEquals(0, $id);
    }

    public function testGetNewestItemId () {
        $this->loadFixtures('default');

        $id = $this->itemMapper->getNewestItemId($this->user);

        $itemId = $this->whereTitleId('no folder');
        $this->assertEquals($itemId, $id);
    }

    public function testFindAllUnreadOrStarred () {
        $this->loadFixtures('default');

        $items = $this->itemMapper->findAllUnreadOrStarred($this->user);
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
