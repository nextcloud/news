<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */


namespace OCA\News\Tests\Integration;

use PHPUnit_Framework_TestCase;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\IAppContainer;

use OCP\IDBConnection;
use OCP\IUserSession;
use OCP\IUserManager;

use OCA\News\AppInfo\Application;
use OCA\News\Tests\Integration\Fixtures\ItemFixture;
use OCA\News\Tests\Integration\Fixtures\FeedFixture;
use OCA\News\Tests\Integration\Fixtures\FolderFixture;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Db\FolderMapper;


abstract class IntegrationTest extends \Test\TestCase {

    protected $user = 'test';
    protected $userPassword = 'test';

    /** @var ItemMapper */
    protected $itemMapper;

    /** @var  FeedMapper */
    protected $feedMapper;

    /** @var FolderMapper */
    protected $folderMapper;

    /** @var IAppContainer */
    protected $container;

    protected function setUp() {
        parent::setUp();
        $app = new Application();
        $this->container = $app->getContainer();
        $this->tearDownUser($this->user);
        $this->setupUser($this->user, $this->userPassword);

        // set up database layers
        $this->itemMapper = $this->container->query(ItemMapper::class);
        $this->feedMapper = $this->container->query(FeedMapper::class);
        $this->folderMapper = $this->container->query(FolderMapper::class);
    }

    protected function findItemByTitle($title) {
        // db logic in app code, negligible since its a test
        $items = $this->itemMapper->where(['title' => $title]);
        $feeds = $this->feedMapper->where(['userId' => $this->user]);

        $feedIds = [];
        foreach ($feeds as $feed) {
            $feedIds[$feed->getId()] = true;
        }

        $result = array_filter($items,
            function (Item $item) use ($feedIds) {
            return array_key_exists($item->getFeedId(), $feedIds);
        });

        // ok so this is funny: array_filter preserves indices, meaning that
        // you can't use 0 as key for the first element return from it :D
        $result = array_values($result)[0];

        return $result;
    }

    protected function findFolderByName($name) {
        return $this->folderMapper->where([
            'userId' => $this->user,
            'name' => $name
        ])[0];
    }

    protected function findFeedByTitle($title) {
        return $this->feedMapper->where([
            'userId' => $this->user,
            'title' => $title
        ])[0];
    }

    /**
     * @param string $name loads fixtures from a given file
     */
    protected function loadFixtures($name) {
        $fixtures = include __DIR__ . '/Fixtures/data/' . $name . '.php';
        if (array_key_exists('folders', $fixtures)) {
            $this->loadFolderFixtures($fixtures['folders']);
        }
        if (array_key_exists('feeds', $fixtures)) {
            $this->loadFeedFixtures($fixtures['feeds']);
        }
    }

    protected function loadFolderFixtures(array $folderFixtures=[]) {
        foreach ($folderFixtures as $folderFixture) {
            $folder = new FolderFixture($folderFixture);
            $folderId = $this->loadFixture($folder);
            $this->loadFeedFixtures($folderFixture['feeds'], $folderId);
        }
    }

    protected function loadFeedFixtures(array $feedFixtures=[], $folderId=0) {
        foreach ($feedFixtures as $feedFixture) {
            $feed = new FeedFixture($feedFixture);
            $feed->setFolderId($folderId);
            $feedId = $this->loadFixture($feed);

            if (!empty($feedFixture['items'])) {
				$this->loadItemFixtures($feedFixture['items'], $feedId);
			}
        }
    }

    protected function loadItemFixtures(array $itemFixtures=[], $feedId) {
        foreach ($itemFixtures as $itemFixture) {
            $item = new ItemFixture($itemFixture);
            $item->setFeedId($feedId);
            $this->loadFixture($item);
        }
    }

    /**
     * Saves a fixture in a database and returns the saved result
     * @param Entity $fixture
     * @return int the id
     */
    protected function loadFixture(Entity $fixture) {
        if ($fixture instanceof FeedFixture) {
            return $this->feedMapper->insert($fixture)->getId();
        } elseif ($fixture instanceof ItemFixture) {
            return $this->itemMapper->insert($fixture)->getId();
        } elseif ($fixture instanceof FolderFixture) {
            return $this->folderMapper->insert($fixture)->getId();
        }

        throw new \InvalidArgumentException('Invalid fixture class given');
    }

    /**
     * Creates and logs in a new ownCloud user
     * @param $user
     * @param $password
     */
    protected function setupUser($user, $password) {
        $userManager = $this->container->query(IUserManager::class);
        $userManager->createUser($user, $password);

        $this->loginAsUser($user);
    }

    /**
     * Removes a user and his News app database entries from the database
     * @param $user
     */
    protected function tearDownUser($user) {
        $userManager = $this->container->query(IUserManager::class);

        if ($userManager->userExists($user)) {
            $userManager->get($user)->delete();
        }

        $this->clearUserNewsDatabase($user);
    }

    /**
     * Deletes all news entries of a given user
     * @param string $user
     */
    protected function clearUserNewsDatabase($user) {
        $sql = [
            'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` IN
              (SELECT `id` FROM `*PREFIX*news_feeds` WHERE `user_id` = ?)',
            'DELETE FROM `*PREFIX*news_feeds` WHERE `user_id` = ?',
            'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?'
        ];

        $db = $this->container->query(IDBConnection::class);
        foreach ($sql as $query) {
            $db->prepare($query)->execute([$user]);
        }
    }

    protected function tearDown() {
        parent::tearDown();
        $this->tearDownUser($this->user);
    }

}