<?php
namespace OCA\News\Tests\Integration;

require_once __DIR__ . '/../../../../lib/base.php';

use \OCA\News\AppInfo\Application;
use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;

class NewsIntegrationTest extends \PHPUnit_Framework_TestCase {

    protected $userId = 'test';
    protected $userPassword = 'test';
    protected $ownCloudVersion;
    protected $container;
    protected $folderMapper;
    protected $feedMapper;
    protected $itemMapper;
    protected $folders = [];
    protected $feeds = [];
    protected $items = [];

    protected function setUp() {
        $this->ownCloudVersion = \OCP\Util::getVersion();
        $this->cleanUp();
        $this->setupUser($this->userId, $this->userPassword);

        $app = new Application();
        $this->container = $app->getContainer();
        $this->itemMapper = $this->container->query('ItemMapper');
        $this->feedMapper = $this->container->query('FeedMapper');
        $this->folderMapper = $this->container->query('FolderMapper');

        $this->loadFixtures(
            $this->folderMapper,
            $this->feedMapper,
            $this->itemMapper
        );
    }


    protected function clearNewsDatabase($user='test') {
        $sql = [
            'DELETE FROM *PREFIX*news_items WHERE feed_id IN ' .
                '(SELECT id FROM *PREFIX*news_feeds WHERE user_id = ?)',
            'DELETE FROM *PREFIX*news_feeds WHERE user_id = ?',
            'DELETE FROM *PREFIX*news_folders WHERE user_id = ?'
        ];

        $db = \OC::$server->getDb();
        foreach ($sql as $query) {
            $db->prepareQuery($query)->execute([$user]);
        }
    }


    protected function loadFixtures($folderMapper, $feedMapper, $itemMapper) {
        $folders = file_get_contents(__DIR__ . '/fixtures/folders.json');
        $feeds = file_get_contents(__DIR__ . '/fixtures/feeds.json');
        $items = file_get_contents(__DIR__ . '/fixtures/items.json');

        $folders = json_decode($folders, true);
        $feeds = json_decode($feeds, true);
        $items = json_decode($items, true);

        // feeds in folders
        foreach($folders as $folder) {
            $newFolder = $this->createFolder($folder);
            $this->folders[$newFolder->getName()] = $newFolder;

            if (array_key_exists($folder['name'], $feeds)) {
                foreach ($feeds[$folder['name']] as $feed) {
                    $feed['folderId'] = $newFolder->getId();
                    $newFeed = $this->createFeed($feed);
                    $this->feeds[$newFeed->getTitle()] = $newFeed;

                    if (array_key_exists($feed['title'], $items)) {
                        foreach ($items[$feed['title']] as $item) {
                            $item['feedId'] = $newFeed->getId();
                            $this->items[$item['title']] =
                                $this->createItem($item);
                        }
                    }
                }
            }
        }

        // feeds in no folders
        if (array_key_exists('no feed', $feeds)) {
            foreach ($feeds['no feed'] as $feed) {
                $feed['folderId'] = 0;
                $this->feeds[] = $this->createFeed($feed);

                if (array_key_exists($feed['title'], $items)) {
                    foreach ($items[$feed['title']] as $item) {
                        $item['feedId'] = $newFeed->getId();
                        $this->items[$item['title']] =
                                $this->createItem($item);
                    }
                }
            }
        }
    }


    private function createFolder($folder) {
        $newFolder = new Folder();
        $newFolder->setName($folder['name']);
        $newFolder->setUserId($this->userId);
        $newFolder->setParentId(0);
        $newFolder->setOpened($folder['opened']);
        $newFolder->setDeletedAt($folder['deletedAt']);
        return $this->folderMapper->insert($newFolder);
    }


    private function createFeed($feed) {
        $newFeed = new Feed();
        $newFeed->setUserId($this->userId);
        $newFeed->setFolderId($feed['folderId']);
        $newFeed->setTitle($feed['title']);
        $newFeed->setUrl($feed['url']);
        $newFeed->setLocation($feed['location']);
        $newFeed->setFaviconLink($feed['faviconLink']);
        $newFeed->setAdded($feed['added']);
        $newFeed->setLink($feed['link']);
        $newFeed->setPreventUpdate($feed['preventUpdate']);
        $newFeed->setDeletedAt($feed['deletedAt']);
        $newFeed->setArticlesPerUpdate($feed['articlesPerUpdate']);
        $newFeed->setLastModified($feed['lastModified']);
        $newFeed->setEtag($feed['etag']);
        return $this->feedMapper->insert($newFeed);
    }


    private function createItem($item) {
        $newItem = new Item();
        $newItem->setFeedId($item['feedId']);
        $newItem->setStatus($item['status']);
        $newItem->setBody($item['body']);
        $newItem->setTitle($item['title']);
        $newItem->setAuthor($item['author']);
        $newItem->setGuid($item['guid']);
        $newItem->setUrl($item['url']);
        $newItem->setPubDate($item['pubDate']);
        $newItem->setLastModified($item['lastModified']);
        $newItem->setEnclosureMime($item['enclosureMime']);
        $newItem->setEnclosureLink($item['enclosureLink']);
        return $this->itemMapper->insert($newItem);
    }


    protected function whenOlderThan($olderThan, $callback) {
        $ocVersion = $this->ownCloudVersion;
        if (version_compare(implode('.', $ocVersion), $olderThan, '<')) {
            $callback();
        }
    }


    protected function setupUser($user='test', $password='test') {
        $userManager = \OC::$server->getUserManager();

        if ($userManager->userExists($user)) {
            $userManager->get($user)->delete();

            $this->whenOlderThan('7.8', function () use ($user, $userManager) {
                $userManager->delete($user);
            });
        }

        $userManager->createUser($user, $password);

        $session = \OC::$server->getUserSession();
        $session->login($user, $password);
    }

    private function cleanUp() {
        $this->clearNewsDatabase($this->userId);
        $this->folders = [];
        $this->feeds = [];
        $this->items = [];
    }

    protected function tearDown() {
        $this->cleanUp();
    }


}