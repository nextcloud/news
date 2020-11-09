<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    Daniel Opitz <dev@copynpaste.de>
 * @copyright Bernhard Posselt 2015
 * @copyright Daniel Opitz 2017
 */

namespace OCA\News\Tests\Integration\Db;

use OCA\News\Db\Feed;
use OCA\News\Tests\Integration\IntegrationTest;
use OCA\News\Tests\Integration\Fixtures\FeedFixture;

class FeedMapperTest extends IntegrationTest
{

    public function testFind()
    {
        $feed = new FeedFixture();
        $feed = $this->feedMapper->insert($feed);

        $fetched = $this->feedMapper->find($this->user, $feed->getId());

        $this->assertInstanceOf(Feed::class, $fetched);
        $this->assertEquals($feed->getLink(), $fetched->getLink());
    }

    public function testFindNotExisting()
    {
        $this->expectException('OCP\AppFramework\Db\DoesNotExistException');
        $this->feedMapper->find($this->user, 0);
    }


    public function testFindAll()
    {
        $feeds = [
            [
                'userId' => $this->user,
                'items' => []
            ],
            [
                'userId' => 'john',
                'items' => []
            ]
        ];
        $this->loadFeedFixtures($feeds);

        $fetched = $this->feedMapper->findAll();

        $this->assertIsArray($fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(Feed::class, $fetched);

        $this->tearDownUser('john');
    }

    public function testFindAllEmpty()
    {
        $feeds = $this->feedMapper->findAll();

        $this->assertIsArray($feeds);
        $this->assertCount(0, $feeds);
    }


    public function testFindAllFromUser()
    {
        $feeds = [
            [
                'userId' => $this->user,
                'items' => []
            ],
            [
                'userId' => 'john',
                'items' => []
            ]
        ];
        $this->loadFeedFixtures($feeds);

        $fetched = $this->feedMapper->findAllFromUser($this->user);

        $this->assertIsArray($fetched);
        $this->assertCount(1, $fetched);
        $this->assertContainsOnlyInstancesOf(Feed::class, $fetched);

        $this->tearDownUser('john');
    }


    public function testFindAllFromUserNotExisting()
    {
        $fetched = $this->feedMapper->findAllFromUser('notexistinguser');

        $this->assertIsArray($fetched);
        $this->assertCount(0, $fetched);
    }


    public function testFindByUrlHash()
    {
        $feed = new FeedFixture(
            [
            'urlHash' => 'someTestHash',
            'title' => 'Some Test Title'
            ]
        );
        $feed = $this->feedMapper->insert($feed);

        $fetched = $this->feedMapper->findByUrlHash($feed->getUrlHash(), $this->user);

        $this->assertInstanceOf(Feed::class, $fetched);
        $this->assertEquals($feed->getTitle(), $fetched->getTitle());
    }

    public function testFindByUrlHashMoreThanOneResult()
    {
        $this->expectException('OCP\AppFramework\Db\MultipleObjectsReturnedException');
        $feed1 = $this->feedMapper->insert(
            new FeedFixture(
                [
                'urlHash' => 'someTestHash'
                ]
            )
        );
        $feed2 = $this->feedMapper->insert(
            new FeedFixture(
                [
                'urlHash' => 'someTestHash'
                ]
            )
        );

        $this->feedMapper->findByUrlHash($feed1->getUrlHash(), $this->user);
    }

    public function testFindByUrlHashNotExisting()
    {
        $this->expectException('OCP\AppFramework\Db\DoesNotExistException');
        $this->feedMapper->findByUrlHash('some random hash', $this->user);
    }


    public function testDelete()
    {
        $this->loadFixtures('default');

        $feeds = $this->feedMapper->findAllFromUser($this->user);
        $this->assertCount(4, $feeds);

        $feed = reset($feeds);

        $items = $this->itemMapper->findAllFeed(
            $feed->getId(), 100, 0, true, false, $this->user
        );
        $this->assertCount(7, $items);

        $this->feedMapper->delete($feed);

        $this->assertCount(3, $this->feedMapper->findAllFromUser($this->user));

        $items = $this->itemMapper->findAllFeed(
            $feed->getId(), 100, 0, true, false, $this->user
        );
        $this->assertCount(0, $items);
    }

    public function testGetToDelete()
    {
        $this->loadFeedFixtures(
            [
            ['deletedAt' => 1],
            ['deletedAt' => 0],
            ['deletedAt' => 1, 'userId' => 'john'],
            ['deletedAt' => 1000]
            ]
        );

        $fetched = $this->feedMapper->getToDelete();

        $this->assertIsArray($fetched);
        $this->assertCount(3, $fetched);
        $this->assertContainsOnlyInstancesOf(Feed::class, $fetched);

        $this->tearDownUser('john');
    }

    public function testGetToDeleteOlderThan()
    {
        $this->loadFeedFixtures(
            [
            ['deletedAt' => 1],
            ['deletedAt' => 0],
            ['deletedAt' => 1, 'userId' => 'john'],
            ['deletedAt' => 1000]
            ]
        );

        $fetched = $this->feedMapper->getToDelete(1000);

        $this->assertIsArray($fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(Feed::class, $fetched);

        $this->tearDownUser('john');
    }

    public function testGetToDeleteUser()
    {
        $this->loadFeedFixtures(
            [
            ['deletedAt' => 1],
            ['deletedAt' => 0],
            ['deletedAt' => 1, 'userId' => 'john'],
            ['deletedAt' => 1000]
            ]
        );

        $fetched = $this->feedMapper->getToDelete(2000, $this->user);

        $this->assertIsArray($fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(Feed::class, $fetched);

        $this->tearDownUser('john');
    }

    public function testGetToDeleteEmpty()
    {
        $fetched = $this->feedMapper->getToDelete();

        $this->assertIsArray($fetched);
        $this->assertCount(0, $fetched);
    }

    public function testDeleteUser()
    {
        $this->loadFixtures('default');

        $this->assertCount(4, $this->feedMapper->findAllFromUser($this->user));

        $items = $this->itemMapper->findAllItems(100, 0, 0, true, false, $this->user);
        $this->assertCount(9, $items);

        $this->feedMapper->deleteUser($this->user);

        $this->assertCount(0, $this->feedMapper->findAllFromUser($this->user));

        $items = $this->itemMapper->findAllItems(100, 0, 0, true, false, $this->user);
        $this->assertCount(0, $items);
    }
}
