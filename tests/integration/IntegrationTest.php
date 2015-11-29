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


namespace OCA\News\Tests\Integration;

use PHPUnit_Framework_TestCase;

use OCP\IDb;
use OCP\IUserSession;
use OCP\IUserManager;

use OCA\News\AppInfo\Application;
use OCA\News\Tests\Integration\Fixtures\Fixture;
use OCA\News\Tests\Integration\Fixtures\ItemFixture;
use OCA\News\Tests\Integration\Fixtures\FeedFixture;
use OCA\News\Tests\Integration\Fixtures\FolderFixture;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Db\FolderMapper;


abstract class IntegrationTest extends PHPUnit_Framework_TestCase {

    protected $user = 'test';
    protected $userPassword = 'test';

    /** @var ItemMapper */
    protected $itemMapper;

    /** @var  FeedMapper */
    protected $feedMapper;

    /** @var FolderMapper */
    protected $folderMapper;

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

    /**
     * Saves a fixture in a database and returns the saved result
     * @param Fixture $fixture
     * @return \OCP\AppFramework\Db\Entity
     */
    protected function loadFixture(Fixture $fixture) {
        if ($fixture instanceof FeedFixture) {
            return $this->feedMapper->insert($fixture);
        } elseif ($fixture instanceof ItemFixture) {
            return $this->itemMapper->insert($fixture);
        } elseif ($fixture instanceof FolderFixture) {
            return $this->folderMapper->insert($fixture);
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

        $session = $this->container->query(IUserSession::class);
        $session->login($user, $password);
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

        $this->clearNewsDatabase($user);
    }

    /**
     * Deletes all news entries of a given user
     * @param string $user
     */
    protected function clearUserNewsDatabase($user) {
        $sql = [
            'DELETE FROM *PREFIX*news_items WHERE feed_id IN ' .
            '(SELECT id FROM *PREFIX*news_feeds WHERE user_id = ?)',
            'DELETE FROM *PREFIX*news_feeds WHERE user_id = ?',
            'DELETE FROM *PREFIX*news_folders WHERE user_id = ?'
        ];

        $db = $this->container->query(IDb::class);
        foreach ($sql as $query) {
            $db->prepareQuery($query)->execute([$user]);
        }
    }

    protected function tearDown() {
        parent::tearDown();
        $this->tearDownUser($this->user);
    }

}