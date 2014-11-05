<?php
namespace OCA\News\Tests\Integration;

require_once __DIR__ . '/../../../../lib/base.php';


class NewsIntegrationTest extends \PHPUnit_Framework_TestCase {

    protected $userId = 'test';
    protected $userPassword = 'test';

    protected function setupNewsDatabase($user='test') {
        $db = \OC::$server->getDb();
        $sql = [
            'DELETE FROM *PREFIX*news_items WHERE feed_id IN ' .
                '(SELECT id FROM *PREFIX*news_feeds WHERE user_id = ?)',
            'DELETE FROM *PREFIX*news_feeds WHERE user_id = ?',
            'DELETE FROM *PREFIX*news_folders WHERE user_id = ?'
        ];

        foreach ($sql as $query) {
            $db->prepareQuery($query)->execute($user);
        }
    }


    protected function setupUser($user='test', $password='test') {
        $userManager = \OC::$server->getUserManager();

        if ($userManager->userExists($user)) {
            $userManager->get($user)->delete();
        }

        $userManager->createUser($user, $password);

        $session = \OC::$server->getUserSession();
        $session->login($user, $password);
    }


    protected function setUp($user='test', $password='test') {
        $this->setupUser($user, $password);
        $this->setupNewsDatabase($user);
    }


}