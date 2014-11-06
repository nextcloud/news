<?php
namespace OCA\News\Tests\Integration;

require_once __DIR__ . '/../../../../lib/base.php';


class NewsIntegrationTest extends \PHPUnit_Framework_TestCase {

    protected $userId = 'test';
    protected $userPassword = 'test';

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


    protected function setupUser($user='test', $password='test') {
        $userManager = \OC::$server->getUserManager();

        if ($userManager->userExists($user)) {
            $userManager->get($user)->delete();
            $userManager->delete($user);
        }

        $userManager->createUser($user, $password);

        $session = \OC::$server->getUserSession();
        $session->login($user, $password);
    }


    protected function setUp() {
        $this->setupUser($this->userId, $this->userPassword);
    }


    protected function tearDown() {
        $this->clearNewsDatabase($this->userId);
    }


}